<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Feds;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Stopwatch\Stopwatch;

/* =========================================================
 * On laptop
 * Fed Only  139 0.50
 * Fed Certs 258 1.00  Quite an increase (because of indexes?)
 * Fed Orgs  138 1.10  Very little increase
 * 
 * At Home
 * Feds  139 3.5
 * Certs 258 9.48 Increase still seems bizare
 * 
 */
class FedsImport01YAMLResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $stopwatch;
    public $duration;
    public $memory;
    
    public $totalFedCount  = 0;
    public $totalOrgCount  = 0;
    public $totalCertCount = 0;
    
    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start('import');
    }
    public function __toString()
    {
        $event = $this->stopwatch->stop('import');
        $this->duration = $event->getDuration();
        $this->memory   = $event->getMemory();
        
        return sprintf(
            "%s %s\n" . 
            "Feds  Insert %d\n" .
            "Certs Insert %d\n" .
            "Duration %.2f %.2fM\n",
            $this->message,
            $this->basename,
            $this->totalFedCount,
            $this->totalCertCount,
            $this->duration / 1000.,
            $this->memory   / 1000000.
        );
    }
}
class FedsImport01YAML
{
    protected $conn;
    protected $results;
    
    public function __construct($conn)
    {
        $this->conn = $conn;
        
        $this->prepareFedInsert    ($conn);
        $this->prepareFedCertInsert($conn);                
    }
    /* ======================================================
     * Reset the database
     */
    public function resetDatabase()
    {
        $conn = $this->conn;
        
        $conn->executeUpdate('DELETE FROM person_fed_certs;' );
      //$conn->executeUpdate('DELETE FROM person_fed_orgs;' );
        $conn->executeUpdate('DELETE FROM person_feds;' );
       
        $conn->executeUpdate('ALTER TABLE person_fed_certs AUTO_INCREMENT = 1;');        
      //$conn->executeUpdate('ALTER TABLE person_fed_orgs  AUTO_INCREMENT = 1;');        
        $conn->executeUpdate('ALTER TABLE person_feds      AUTO_INCREMENT = 1;');     
    }
    /* =========================================================================
     * Process PersonFedCert
     *    (:fedId,:role,:badge.:badgeUser,:upgrading,'Active')      
     */
    protected $statementFedCertInsert;
    
    protected function prepareFedCertInsert($conn)
    {
        $sql = <<<EOT
INSERT INTO person_fed_certs 
    ( person_fed_id, role, badge, badge_user, upgrading, mem_year, status)
VALUES
    (:person_fed_id,:role,:badge,:badge_user,:upgrading,:mem_year, 'Active')
;
EOT;
        $this->statementFedCertInsert = $conn->prepare($sql);
    }
    protected function processFedCert($fed,$cert)
    {
        $cert['person_fed_id'] = $fed['id'];
        $cert['mem_year']      = $fed['mem_year'];
        
      //print_r($cert); die();
        
        $this->statementFedCertInsert->execute($cert);
        $this->results->totalCertCount++;
    }
    /* =========================================================================
     * Process PersonFed
     */
    protected $statementFedInsert;
    
    protected function prepareFedInsert($conn)
    {
        $sql = <<<EOT
INSERT INTO person_feds 
    ( person_id, fed, fed_role, fed_key, org_key, mem_year,status)
VALUES
    (:person_id,:fed,:fed_role,:fed_key,:org_key,:mem_year,'Active')       
;
EOT;
        $this->statementFedInsert = $conn->prepare($sql);
    }
    protected function processFed($fed)
    {
        $fedx = $fed;
        
        // AYSOV to AYSO
        $fedx['fed'] = substr($fedx['fed_role'],0,4);
        
        unset($fedx['certs']);
        
        $this->statementFedInsert->execute($fedx);
        
        $fedx['id'] = $this->conn->lastInsertId();
        
        $this->results->totalFedCount++;
        
        foreach($fed['certs'] as $cert)
        {
            $this->processFedCert($fedx,$cert);
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
    /* =========================================================================
     * Process PersonFedOrg
     * (:fedId,:role,:orgKey,'Active')   
     * *** Not longer used
     */
    protected function processFedOrg($fedId,$org)
    {
        $orgx = array(
            'fedId'  => $fedId,
            'role'   => $org['role'],
            'orgKey' => $org['org_id'],
        );
        $this->insertOrgStatement->execute($orgx);
        $this->results->totalOrgCount++;
    }
}
?>
