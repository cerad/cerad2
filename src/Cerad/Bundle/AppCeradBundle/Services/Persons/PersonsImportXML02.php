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
    
    public $totalPersonCount  = 0;
    public $insertPersonCount = 0;
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
        
        $tables = array(
            'person_fed_certs','person_fed_orgs','person_feds',
            'person_plans','person_persons','persons',
        );
        foreach($tables as $table)
        {
            $conn->executeUpdate("DELETE FROM $table;" );
            $conn->executeUpdate("ALTER TABLE $table AUTO_INCREMENT = 1;");        
        }  
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
     * Inserting a brand new person.  Nothing existing
     */
    protected function insertPerson($person)
    {
        $this->results->insertPersonCount++;
        
        // Insert the person
        $person['id'] = null;
        $personData = array();
        foreach($this->getTableColumnNames('persons') as $key) $personData[$key] = $person[$key];

        $personInsertStatement = $this->getTableInsertStatement('persons');
        $personInsertStatement->execute($personData);
        $person['id'] = $this->conn->lastInsertId();
        
        echo sprintf("%d %s\n",$person['id'],$person['name_full']);
        
        // Now the feds
        foreach($person['feds'] as $fed)
        {
            $fed['id'] = $fed['fed_id'];
            $fed['person_id'] = $person['id'];
            $fedData = array();
            foreach($this->getTableColumnNames('person_feds') as $key) $fedData[$key] = $fed[$key];
            
            $fedInsertStatement = $this->getTableInsertStatement('person_feds');
            $fedInsertStatement->execute($fedData);
          //$fed['id'] = $this->conn->lastInsertId();

            // Certs
            foreach($fed['certs'] as $cert)
            {
                $cert['id'] = null;
                $cert['fed_id'] = $fed['fed_id'];
                
                $certData = array();
                foreach($this->getTableColumnNames('person_fed_certs') as $key) $certData[$key] = $cert[$key];
            
                $certInsertStatement = $this->getTableInsertStatement('person_fed_certs');
                $certInsertStatement->execute($certData);
            }
            // Orgs
            foreach($fed['orgs'] as $org)
            {
                $org['id'] = null;
                $org['fed_id'] = $fed['fed_id'];
                
                $orgData = array();
                foreach($this->getTableColumnNames('person_fed_orgs') as $key) $orgData[$key] = $org[$key];
            
                $orgInsertStatement = $this->getTableInsertStatement('person_fed_orgs');
                $orgInsertStatement->execute($orgData);
            }
        }
        // Plans
        foreach($person['plans'] as $plan)
        {
            $plan['id'] = null;
            $plan['person_id'] = $person['id'];
            $planData = array();
            foreach($this->getTableColumnNames('person_plans') as $key) $planData[$key] = $plan[$key];
            
            $planData['basic'] = serialize($planData['basic']);
            $planData['level'] = serialize($planData['level']);
            $planData['avail'] = serialize($planData['avail']);
            
            $planInsertStatement = $this->getTableInsertStatement('person_plans');
            $planInsertStatement->execute($planData);
        }
    }
    /* =========================================================================
     * Extract PersonPlan
     */
    protected function extractPersonPlan($reader)
    {
        $plan = $reader->getAttributes();
        $plan['basic'] = array();
        $plan['level'] = null;
        $plan['avail'] = null;
        $plan['notes'] = null;
        
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
     * Extract PersonFed
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
     * Extract Person
     */
    protected function extractPerson($reader)
    {
        $this->results->totalPersonCount++;
        
        $person = $reader->getAttributes();
        $person['feds']    = array();
        $person['plans']   = array();
        $person['users']   = array();
        $person['persons'] = array();
        
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
                    
                    case 'person_person':
                        $child = $reader->getAttributes();
                        $person['persons'][] = $child;
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
            
            $this->insertPerson($person);
            
          //$this->processPerson($person);
          //print_r($person); die();
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
