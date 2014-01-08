<?php
namespace Cerad\Bundle\EaysoBundle\Services\Feds;

use Symfony\Component\Stopwatch\Stopwatch;

/* ==============================================================
 * Basic design question is: 
 * Do we pull as much as possible (gender, region etc) from the cert record
 * Or do we pretty much require importing the vol record?
 * 
 * Cert Import Total 38330, MY 19899, Fed 127 Badge 169
 * No joins     7742
 * Join person: 8400
 * Join org:    9000
 * 
 * Adding in person,cert,region updates/inserts 9400
 */
/* ==============================================================
 * For lack of a better word, use sync to describe the process of
 * loading eayso information from eayso reports
 */
class FedsSyncResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $totalCertCount       = 0;
    public $totalCertMYCount     = 0;
    public $totalCertFedCount    = 0;
    public $totalCertBadgeCount  = 0;
    public $totalCertUpdateCount = 0;
    public $totalCertInsertCount = 0;
    
    public $totalRegionInsertCount = 0;
    public $totalRegionUpdateCount = 0;
    
    public $totalPersonUpdateCount = 0;
    
    public $duration;
    
    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start('sync');
    }
}
class FedsSync
{
    protected $conn;
    protected $results;
    protected $certRepo;
    
    // TODO: Bench mark prepared statements
    protected $selectFedStatement     = null;
    protected $updateFedStatement     = null;
    
    protected $selectFedCertStatement = null;
    protected $insertFedCertStatement = null;
    protected $updateFedCertStatement = null;
    
    protected $insertFedRegionStatement = null;
    protected $updateFedRegionStatement = null;
    
    protected $updatePersonStatement = null;
    
    public function __construct($conn,$certRepo)
    {
        $this->conn = $conn;
        $this->certRepo = $certRepo;
        
        /* ========================================================
         * PersonFed - The select statement joins person and the region
         */
        $selectFedSql = <<<EOT
SELECT 
    fed.id        AS fed_idx,
    fed.verified  AS fed_verified,
                
    person.id     AS person_idx,
    person.dob    AS person_dob,
    person.gender AS person_gender,
                
    org.id        AS org_idx,
    org.org_id    AS org_region,
    org.mem_year  AS org_mem_year,
    org.verified  AS org_verified
                
FROM      person_feds     AS fed
LEFT JOIN persons         AS person ON person.id  = fed.person_id
LEFT JOIN person_fed_orgs AS org    ON org.fed_id = fed.id AND org.role = 'Region'
WHERE fed.fed_id = :fedId
;
EOT;

        $this->selectFedStatement = $conn->prepare($selectFedSql);
        
        $this->updateFedStatement = 
            $conn->prepare("UPDATE id person_feds SET verified = 'Yes' WHERE id = :id;\n");
        
        /* ===============================================================
         * PersonFedCert
         */
        $this->selectFedCertStatement = 
            $conn->prepare("SELECT * FROM person_fed_certs WHERE fed_id = :fedIdx AND role = :role;\n");
        
        $updateFedCertSql = <<<EOT
UPDATE person_fed_certs 
SET 
    badge     = :badge,
    date_cert = :dateCert,
    verified  = :verified 
WHERE id=:id
;
EOT;
        $this->updateFedCertStatement = $conn->prepare($updateFedCertSql);
        
        $insertFedCertSql = <<<EOT
INSERT INTO person_fed_certs 
    (fed_id,role,badge,badgex,date_cert,status,verified)
VALUES
    (:fedId,:role,:badge,:badgex,:dateCert,'Active','Yes')             
;
EOT;
        $this->insertFedCertStatement = $conn->prepare($insertFedCertSql);

        /* =================================================================
         * Org Region
         */
        $updateRegionSql = <<<EOT
UPDATE person_fed_orgs 
SET 
    mem_year  = :memYear,
    verified  = :verified 
WHERE id=:id
;
EOT;
        $this->updateRegionStatement = $conn->prepare($updateRegionSql);
        
        $insertRegionSql = <<<EOT
INSERT INTO person_fed_orgs 
    (fed_id,role,org_id,mem_year,status,verified)
VALUES
    (:fedId,'Region',:orgId,:memYear,'Active',:verified)          
;
EOT;
        $this->insertRegionStatement = $conn->prepare($insertRegionSql);
        
        /* =================================================================
         * Person
         */
        $selectPersonSql = <<<EOT
SELECT
    person.id     AS person_id,
    person.gender AS person_gender,
    person.dob    AS person_dob
FROM persons AS person
WHERE person_id = :id
;
EOT;
        $this->selectPersonStatement = $conn->prepare($selectPersonSql); // Not currently used
        
        $updatePersonSql = <<<EOT
UPDATE persons 
SET gender = :gender
WHERE id = :id
;
EOT;
        $this->updatePersonStatement = $conn->prepare($updatePersonSql);
        
    }
    
    /* ===============================================================
     * TODO: Benchmark datetime conversion
     */
    protected function processDate($date)
    {
        $parts = explode('/',$date);
        if (count($parts) != 3) return null;
        return sprintf('%04u-%02u-%02u',$parts[2],$parts[0],$parts[1]);
    }
    /* ==========================================================================
     * Update existing person gender
     */
    protected function updateCeradPerson($eaysoCert,$ceradFed)
    {
        // Needs to be linked
        $ceradPersonIdx = $ceradFed['person_idx'];
        if (!$ceradPersonId) die('missing person id');
                
        $needUpdate = false;
        
        // If the cerad person gender has a value then leave it alone
        $ceradGender = $ceradFed['person_gender'];
        if ((!$ceradGender) && ($ceradGender != $eaysoCert['gender'])) $needUpdate = true; 
        
        if (!$needUpdate) return;
        
        $params = array(
            'id'     => $ceradPersonIdx,
            'gender' => $eaysoCert['gender'],
        );
        $this->updatePersonStatement->execute($params);
        $this->results->totalPersonUpdateCount++;
        
    }
    /* ==========================================================================
     * Update/Insert Region
     */
    protected function updateCeradRegion($eaysoCert,$ceradFed)
    {
        // Transform region number
        $eaysoRegion = sprintf('AYSOR%04u',(int)$eaysoCert['region']);
        if ($eaysoRegion == 'AYSOR0000')
        {
           die('*** Missing eayso region number');            
        }
        // Only MY will get here
        $eaysoMemYear = $eaysoCert['mem_year'];
        $updateMemYear = $eaysoMemYear;
        
        // Needs to be linked
        $ceradRegionIdx = $ceradFed['org_idx'];
        if (!$ceradRegionIdx) 
        {
            $params = array
            (
                'fedId'    => $ceradFed['fed_idx'],
                'orgId'    => $eaysoRegion,
                'memYear'  => $eaysoMemYear,
                'verified' => 'Yes'    
            );
            $this->insertRegionStatement->execute($params);
            $this->results->totalRegionInsertCount++;
            return;
          //print_r($ceradFed);
          //print_r($eaysoCert);
          //die();
        }
        
        $needUpdate = false;
        
        // Check mem year
        $ceradMemYear = $ceradFed['org_mem_year'];
        if ($eaysoMemYear > $ceradMemYear) $needUpdate = true;
        else                               $updateMemYear = $ceradMemYear;
        
        // Update verified if the regions match
        $ceradRegion = $ceradFed['org_region'];
        if ($eaysoRegion == $ceradRegion && $ceradFed['org_verified'] != 'Yes') 
        {
            $updateVerified = 'Yes';
            $needUpdate = true;
        }
        // Otherwise leave verified alone
        else $updateVerified = $ceradFed['org_verified'];
        
        if (!$needUpdate) return;
        
        $params = array(
            'id'       => $ceradRegionIdx,
            'memYear'  => $updateMemYear,
            'verified' => $updateVerified, 
        );
        $this->updateRegionStatement->execute($params);
        $this->results->totalRegionUpdateCount++;
        return;
        
        print_r($ceradFed);
        print_r($eaysoCert);
        die('Region Update');
        
    }
    /* ==========================================================================
     * Update existing cert record
     * 
     * TODO: Try storing date first certified for role
     */
    protected function updateCeradCert($eaysoCert,$eaysoRole,$eaysoBadge,$ceradCert)
    {
        // Start by checking badges
        $ceradBadge = $ceradCert['badge'];
        if ($this->certRepo->compareBadges($eaysoRole,$eaysoBadge,$ceradBadge) < 0) return;
        
        // Track changes to avoid unnecessary updates
        $needUpdate = false;
        
        // New badge
        if ($ceradBadge != $eaysoBadge) $needUpdate = true;
        
        // Adjust the verified
        if ($ceradCert['verified'] == 'No') $needUpdate = true;
        
        // Adjust the date
        $eaysoDateCert = $this->processDate($eaysoCert['date_cert']);
        $ceradDateCert = $ceradCert['date_cert'];
        if ($ceradDateCert != $eaysoDateCert)  $needUpdate = true;
        
        // Any changes?
        if (!$needUpdate) return;
        
        // Need update, just make suree all three updateable values are set
        $params = array(
            'id'       => $ceradCert['id'],
            'badge'    => $eaysoBadge,
            'verified' => 'Yes',
            'dateCert' => $eaysoDateCert,
        );
        $this->updateFedCertStatement->execute($params);
        $this->results->totalCertUpdateCount++;
    }
    /* ==========================================================================
     * process individual person_fed_cert record
     */
    protected function processEaysoCertBadge($eaysoCert,$eaysoRole,$eaysoBadge,$ceradFed)
    {
        // Skip the others
        if ($eaysoRole == 'Other') return;
        $this->results->totalCertBadgeCount++;
        
        // Update or insert?
        $fedIdx = $ceradFed['fed_idx'];
        $this->selectFedCertStatement->execute(array('fedIdx' => $fedIdx, 'role' => $eaysoRole));   
        $rows = $this->selectFedCertStatement->fetchAll();
        if (count($rows))
        {
            return $this->updateCeradCert($eaysoCert,$eaysoRole,$eaysoBadge,$rows[0]);
        }
        
        // Setup for insert
        $params = array
        (
            'fedId'    => $fedIdx,
            'role'     => $eaysoRole,
            'badge'    => $eaysoBadge,
            'badgex'   => $eaysoBadge,
            'dateCert' => $this->processDate($eaysoCert['date_cert']),
        );
        $this->insertFedCertStatement->execute($params);
        $this->results->totalCertInsertCount++;
        return;
    }
    
    /* ==========================================================================
     * Process cert information
     */
    protected function processEaysoCert($eaysoCert)
    {
        $this->results->totalCertCount++;
        
        // Filter mem year
        // TODO: regex for MYdddd
        $eaysoMemYear = $eaysoCert['mem_year'];
        if (substr($eaysoMemYear,0,2) != 'MY') return;
        $this->results->totalCertMYCount++;
        
        // Lookup badges
        $eaysoBadges = $this->certRepo->findByCertDesc($eaysoCert['cert_desc']);
        if (count($eaysoBadges) < 1) return;
        
        // Look up fed
        $eaysoFedId = 'AYSOV' . $eaysoCert['fed_id'];
        $this->selectFedStatement->execute(array('fedId' => $eaysoFedId));   
        $rows = $this->selectFedStatement->fetchAll();
        if (count($rows) != 1) return;
        $ceradFed = $rows[0];
        
        $this->results->totalCertFedCount++;
        
        // Process badges
        foreach($eaysoBadges as $eaysoRole => $eaysoBadge)
        {
            $this->processEaysoCertBadge($eaysoCert,$eaysoRole,$eaysoBadge,$ceradFed);
        }
        // Update person record (dob, gender etc)
        $this->updateCeradPerson($eaysoCert,$ceradFed);
        $this->updateCeradRegion($eaysoCert,$ceradFed);
    }
    /* ==========================================================================
     * Main entry point
     * $params['filepath']
     * $params['basename']
     */
    public function process($params)
    {   
        // Param stuff
        $this->results = $results = new FedsSyncResults();
        $results->filepath = $params['filepath'];
        $results->basename = $params['basename'];
                
        // Open
        $fp = fopen($params['filepath'],'rt');
        if (!$fp) return $results;
        
        $headers = fgetcsv($fp);
        $indexes = $this->processHeaders($headers);
        
        while($row = fgetcsv($fp))
        {
            $cert = array();
            foreach($indexes as $key => $index)
            {
                $eaysoCert[$key] = $row[$index];
            }
            $this->processEaysoCert($eaysoCert);
          //print_r($eaysoCert); die();
        }
        
        // Done
        fclose($fp);
        $results->message = "Sync completed";
        
        $event = $results->stopwatch->stop('sync');
        $results->duration = $event->getDuration();
        
        return $results;
        
    }
    protected function processHeaders($headers)
    {
        $map = array(    
            'AYSOID'            => 'fed_id',
            'Name'              => 'name_full',
            'State'             => 'address_state',
            'HomePhone'         => 'phone_home',
            'BusinessPhone'     => 'phone_work',
            'Email'             => 'email',
            'CertificationDesc' => 'cert_desc',
            'Gender'            => 'gender',
            'SectionAreaRegion' => 'sar',
            'CertDate'          => 'date_cert',
            'RegionNumber'      => 'region',
            'Membership Year'   => 'mem_year',
        );
        $indexes = array();
        $index = 0;
        foreach($headers as $header)
        {
            if (isset($map[$header])) $indexes[$map[$header]] = $index;
            $index++;
        }
        // Should probably verify have all the expected columns
        
        return $indexes;
    }
}
?>
