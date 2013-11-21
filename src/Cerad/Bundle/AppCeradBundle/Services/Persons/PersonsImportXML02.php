<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Persons;

class MyXMLReader extends \XMLReader
{
    // Empty strings return null
    public function getAttribute($name)
    {
        $value = parent::getAttribute($name);
        
        if (strlen($value)) return $value;
        
        if (!$value) $value = null;
        
        return $value;
    }
    // Try return all attributes as an array
    public function getAttributes()
    {
        $attrs = array();
        while($this->moveToNextAttribute())
        {
            $value = $this->value;
            if (!strlen($value)) $value = null;
            $attrs[$this->name] = $value;
        }
        return $attrs;
    }
}
class PersonsImportXML02Results
{
    public $message;
    public $filepath;
    public $basename;
    
    public $totalPersonCount = 0;
}
class PersonsImportXML02
{
    protected $conn;
    protected $reader;
    
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    /* ======================================================
     * Reset the database
     */
    public function resetDatabase()
    {
        $conn = $this->conn;
        
        $conn->executeUpdate('DELETE FROM person_fed_certs;' );
        $conn->executeUpdate('DELETE FROM person_fed_orgs;' );
        $conn->executeUpdate('DELETE FROM person_feds;' );
        $conn->executeUpdate('DELETE FROM persons;' );
       
        $conn->executeUpdate('ALTER TABLE person_fed_certs AUTO_INCREMENT = 1;');        
        $conn->executeUpdate('ALTER TABLE person_fed_orgs  AUTO_INCREMENT = 1;');        
        $conn->executeUpdate('ALTER TABLE person_feds      AUTO_INCREMENT = 1;');        
        $conn->executeUpdate('ALTER TABLE persons          AUTO_INCREMENT = 1;');        
    }
    /* ======================================================
     * Statement functions
     * Load column names from the database
     */    
    protected $tableColumnNames = array();
    protected $tableInsertStatements = array();
    protected $tableUpdateStatements = array();
    
    protected function getTableColumnNames($tableName)
    {
        if (isset($this->tableColumnNames[$tableName])) return $this->tableColumnNames[$tableName];
        
        $columns = $this->conn->getSchemaManager()->listTableColumns($tableName);
        
        $colNames = array();
        foreach($columns as $column)
        {
            $colNames[] = $column->getName();  // getType
        }
        return $this->tableColumnNames[$tableName] = $colNames;
    }
    protected function getTableInsertStatement($tableName)
    {
        if (isset($this->tableInsertStatements[$tableName])) return $this->tableInsertStatements[$tableName];
        
        $colNames = $this->getTableColumnNames($tableName);
        
        $sql = sprintf("INSERT INTO %s \n(%s)\nVALUES(:%s);",
            $tableName,
            implode(',', $colNames),
            implode(',:',$colNames)
        );
        return $this->tableInsertStatements[$tableName] = $this->conn->prepare($sql);
    }
    /* =========================================================================
     * Process PersonFed elements
     */
    protected function extractPersonPlan($reader)
    {
        $plan = $reader->getAttributes();
        $plan['basic'] = array();
        
        while($reader->read() && $reader->name != 'person_plan')
        {
            if ($reader->nodeType == \XMLReader::ELEMENT)
            {    
                switch($reader->name)
                {
                    case 'person_plan_basic':
                        $plan['basic'] = $reader->getAttributes();
                        break;
                 }
            }
        }
        return $plan;
    }
    /* =========================================================================
     * Process PersonFed elements
     */
    protected function extractPersonFed($reader)
    {
        $fed = $reader->getAttributes();

        $fed['certs']  = array();
        $fed['orgs']   = array();
        
        // Might be fooling myself here, could be consuming subsequent person_feds
        while($reader->read() && $reader->name != 'person_fed')
        {
            // Avoid getting the closing element tags
            if ($reader->nodeType == \XMLReader::ELEMENT)
            {    
                switch($reader->name)
                {
                    case 'person_fed_cert':
                        $cert = $reader->getAttributes();
                        $fed['certs'][] = $cert;
                        break;
                    
                    case 'person_fed_org':
                        $org = $reader->getAttributes();
                        $fed['orgs'][] = $org;
                        break;
                }
            }
        }
        return $fed;
    }
    /* ==========================================================================
     * Process a person and all nested records
     * Use simpleXml to get complete record - Nope 
     */
    protected function extractPerson($reader)
    {
        $this->results->totalPersonCount++;
        
        $person = $reader->getAttributes();
        $person['feds']  = array();
        $person['plans'] = array();
        $person['users'] = array();
        
        // Read through all the sub nodes until hit person END_ELEMENT
        while($reader->read() && $reader->name !== 'person')
        {
            // Avoid getting the closing element tags
            if ($reader->nodeType == \XMLReader::ELEMENT)
            {    
                switch($reader->name)
                {
                    case 'person_fed':
                        $person['feds'][] = $this->extractPersonFed($reader);
                        break;
                        
                    case 'person_plan':
                        $person['plans'][] = $this->extractPersonPlan($reader);
                        break;
                    
                    case 'person_user':
                        $user = $reader->getAttributes();
                        $person['users'][] = $user;
                        break;                    
                }
            }
        }
        return $person;
    }
    /* ==========================================================================
     * Main entry point
     * $params['filepath']
     * $params['basename']
     */
    public function process($params)
    {   
        $this->resetDatabase();
        
        $this->results = $results = new PersonsImportXML02Results();
        $results->filepath = $params['filepath'];
        $results->basename = $params['basename'];
        
        // Open
        $this->reader = $reader = new MyXMLReader();
        $status = $reader->open($params['filepath'],null,LIBXML_COMPACT | LIBXML_NOWARNING);
        if (!$status)
        {
            $results->message = sprintf("Unable to open: %s",$params['filepath']);
            return $results;
        }
        // Export details
        if (!$reader->next('export')) 
        {
            $results->message = '*** Not a Export file';
            $reader->close();
            return $results;
        }
        // Verify report type
        $results->name = $reader->getAttribute('name');
        
        // Persons collection
        // Can't do a next for sub trees?
        while($reader->read() && $reader->name !== 'person');
        
        // Individual Person
        //$reader->read();
        while($reader->name == 'person')
        {
            $person = $this->extractPerson($reader);
            
          //$this->processPerson($person);
            print_r($person); die();
            // On to the next one
            // Done by processPerson
            $reader->next('person');
        }
        
        // Done
        $reader->close();
        $results->message = "Import completed";
        return $results;
        
    }
}
?>
