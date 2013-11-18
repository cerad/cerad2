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
}
class PersonsImportXMLResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $totalPersonCount = 0;
}
class PersonsImportXML
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
     * Process PersonFedOrg elements
     */
    protected function processPersonFedOrg($reader,$personFed)
    {
        $personFedOrg = array();
        foreach($this->getTableColumnNames('person_fed_orgs') as $key)
        {
            $personFedOrg[$key] = $reader->getAttribute($key);;
        }
        $personFedOrg['fed_id'] = $personFed['id'];
        
        $personFedOrgInsertStatement = $this->getTableInsertStatement('person_fed_orgs');
        $personFedOrgInsertStatement->execute($personFedOrg);
    }
    /* =========================================================================
     * Process PersonFedCert elements
     */
    protected function processPersonFedCert($reader,$personFed)
    {
        $personFedCert = array();
        foreach($this->getTableColumnNames('person_fed_certs') as $key)
        {
            $personFedCert[$key] = $reader->getAttribute($key);;
        }
        $personFedCert['fed_id'] = $personFed['id'];
        
        $personFedCertInsertStatement = $this->getTableInsertStatement('person_fed_certs');
        $personFedCertInsertStatement->execute($personFedCert);
        
        // Not sure if really need this but guess it doesn't hurt
        // It does hurt, the second cert is consumed
        // Possible because person_fed_cert has only attributes?
        //while($reader->read() && $reader->name !== 'person_fed_cert') {}
    }
    /* =========================================================================
     * Process PersonFed elements
     */
    protected function processPersonFed($reader,$person)
    {
        $personFed = array(
            'id'          => $reader->getAttribute('fed_id'),
            'person_id'   => $person['id'],
            'fed_role_id' => $reader->getAttribute('fed_role_id'),
            'status'      => $reader->getAttribute('status'),
            'verified'    => $reader->getAttribute('verified'),
        );
        $personFedInsertStatement = $this->getTableInsertStatement('person_feds');
        $personFedInsertStatement->execute($personFed);
        
        // This only works for autoinc/sequences
      //$personFed['id'] = $this->conn->lastInsertId();
        
        // Might be fooling myself here, could be consuming subsequent person_feds
        while($reader->read() && $reader->name != 'person_fed')
        {
            // Avoid getting the closing element tags
            if ($reader->nodeType == \XMLReader::ELEMENT)
            {    
                switch($reader->name)
                {
                    case 'person_fed_cert':
                      //echo sprintf("%s person_fed_cert\n",$personFed['id']);
                        $this->processPersonFedCert($reader,$personFed);
                        break;
                    
                    case 'person_fed_org':
                        $this->processPersonFedOrg($reader,$personFed);
                        break;
                }
            }
        }    
    }
    /* ==========================================================================
     * Process a person and all nested records
     */
    protected function processPerson($reader)
    {
        $this->results->totalPersonCount++;
        
        $person = array();
        foreach($this->getTableColumnNames('persons') as $key)
        {
            $person[$key] = $reader->getAttribute($key);;
        }
        $personInsertStatement = $this->getTableInsertStatement('persons');
        $personInsertStatement->execute($person);
        $person['id'] = $this->conn->lastInsertId();
                
        // Read through all the sub nodes until hit person END_ELEMENT
        while($reader->read() && $reader->name !== 'person')
        {
            // Avoid getting the closing element tags
            if ($reader->nodeType == \XMLReader::ELEMENT)
            {    
                switch($reader->name)
                {
                    case 'person_fed':
                        $this->processPersonFed($reader,$person);
                        break;
                    
                    case 'person_fed_cert':
                    case 'person_fed_certs':
                    case 'person_plan':
                    case 'person_user':
                      //echo sprintf("%s\n",$reader->name);
                }
            }
        }
        // \XMLReader::END_ELEMENT = 15
        // echo sprintf("Person type %d\n",$reader->nodeType);
    }
    /* ==========================================================================
     * Main entry point
     * $params['filepath']
     * $params['basename']
     */
    public function process($params)
    {   
        $this->resetDatabase();
        
        $this->results = $results = new PersonsImportXMLResults();
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
            $this->processPerson($reader);
            
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
