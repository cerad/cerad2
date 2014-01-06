<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Feds;

use Symfony\Component\Yaml\Yaml;

class FedsImport01YAMLResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $totalFedCount = 0;
}
class FedsImport01YAML
{
    protected $conn;
    protected $results;
    
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
       
        $conn->executeUpdate('ALTER TABLE person_fed_certs AUTO_INCREMENT = 1;');        
        $conn->executeUpdate('ALTER TABLE person_fed_orgs  AUTO_INCREMENT = 1;');        
        $conn->executeUpdate('ALTER TABLE person_feds      AUTO_INCREMENT = 1;');     
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
     * Process PersonFedOrg
     */
    protected function processFedOrg($id,$org)
    {
        $org['id'] = null;
        $org['fed_id'] = $id;
        
        if (substr($org['mem_year'],0,2) == 'FS')
        {
            $org['mem_year'] = 'MY' . substr($org['mem_year'],2);
        }
        $orgInsertStatement = $this->getTableInsertStatement('person_fed_orgs');
        $orgInsertStatement->execute($org);    
    }
    /* =========================================================================
     * Process PersonFedCert
     */
    protected function processFedCert($id,$cert)
    {
        $cert['id'] = null;
        $cert['fed_id'] = $id;
        
        $certInsertStatement = $this->getTableInsertStatement('person_fed_certs');
        $certInsertStatement->execute($cert);    
    }
    /* =========================================================================
     * Process PersonFed
     */
    protected function processFed($fed)
    {
        $fedx = $fed;
        $fedx['id'] = null;
        unset($fedx['certs']);
        unset($fedx['orgs' ]);
      
        $fedInsertStatement = $this->getTableInsertStatement('person_feds');
        $fedInsertStatement->execute($fedx);
        
        $id = $this->conn->lastInsertId();
        
        $this->results->totalFedCount++;
        
        foreach($fed['certs'] as $cert)
        {
            $this->processFedCert($id,$cert);
        }
        foreach($fed['orgs'] as $org)
        {
            $this->processFedOrg($id,$org);
        }
        return;
        
    }
    /* ==========================================================================
     * Main entry point
     * $params['filepath']
     * $params['basename']
     */
    public function process($params)
    {   
        $this->resetDatabase();
        
        $this->results = $results = new FedsImport01YAMLResults();
        $results->filepath = $params['filepath'];
        $results->basename = $params['basename'];
        
        // Load
        $yaml = Yaml::parse(file_get_contents($params['filepath']));
        $feds = $yaml['feds'];
        foreach($feds as $fed)
        {
            $this->processFed($fed);
        }
        
        // Done
        $results->message = "Import completed";
        return $results;
        
    }
}
?>
