<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Feds;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Stopwatch\Stopwatch;

/* =========================================================
 * On laptop
 * Fed Only  139 0.50
 * Fed Certs 258 1.00  Quite an increase (because of indexes?)
 * Fed Orgs  138 1.10  Very little increase
 */
class FedsImport01YAMLResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $stopwatch;
    public $duration;
    
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
        return sprintf(
            "%s %s\n" . 
            "Total Feds  %d\n" .
            "Total Orgs  %d\n" .
            "Total Certs %d\n" .
            "Duration %.2f\n",
            $this->message,
            $this->basename,
            $this->totalFedCount,
            $this->totalOrgCount,
            $this->totalCertCount,
            $this->duration / 1000.
        );
    }
}
class FedsImport01YAML
{
    protected $conn;
    protected $results;
    
    protected $fedImportStatement;
    protected $fedCertImportStatement;
    
    public function __construct($conn)
    {
        $this->conn = $conn;
        
        /* ===========================================================
         * person_feds
         */
        $insertFedSql = <<<EOT
INSERT INTO person_feds 
    (person_id,fed,fed_role,fed_key,status)
VALUES
    (:personId,'AYSO','AYSOV',:fedKey,'Active')             
;
EOT;
        $this->insertFedStatement = $conn->prepare($insertFedSql);
        
        /* ===========================================================
         * person_fed_certs
         */
        $insertCertSql = <<<EOT
INSERT INTO person_fed_certs 
    (fed_id,role,badge,badge_user,upgrading,status)
VALUES
    (:fedId,:role,:badge,:badgeUser,:upgrading,'Active')             
;
EOT;
        $this->insertCertStatement = $conn->prepare($insertCertSql);
        
        /* ===========================================================
         * person_fed_orgs
         */
        $insertOrgSql = <<<EOT
INSERT INTO person_fed_orgs 
    (fed_id,role,org_key,status)
VALUES
    (:fedId,:role,:orgKey,'Active')             
;
EOT;
        $this->insertOrgStatement = $conn->prepare($insertOrgSql);
        
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
    /* =========================================================================
     * Process PersonFedOrg
     * (:fedId,:role,:orgKey,'Active')   
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
    /* =========================================================================
     * Process PersonFedCert
     *    (:fedId,:role,:badge.:badgeUser,:upgrading,'Active')      
     */
    protected function processFedCert($fedId,$cert)
    {
        $certx = array(
            'fedId'     => $fedId,
            'role'      => $cert['role'],
            'badge'     => $cert['badge'],
            'badgeUser' => $cert['badgex'],
            'upgrading' => $cert['upgrading'],
            
        );
        $this->insertCertStatement->execute($certx);
        $this->results->totalCertCount++;
    }
    /* =========================================================================
     * Process PersonFed
     */
    protected function processFed($fed)
    {
        $fedx = array(
            'personId' => $fed['person_id'],
            'fedKey'   => $fed['fed_id']
        );
        $this->insertFedStatement->execute($fedx);
        
        $fedId = $this->conn->lastInsertId();
        
        $this->results->totalFedCount++;
        
        foreach($fed['certs'] as $cert)
        {
            $this->processFedCert($fedId,$cert);
        }
        foreach($fed['orgs'] as $org)
        {
            $this->processFedOrg($fedId,$org);
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
        
        $event = $results->stopwatch->stop('import');
        $results->duration = $event->getDuration();

        return $results;
        
    }
}
?>
