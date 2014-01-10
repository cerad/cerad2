<?php
namespace Cerad\Bundle\EaysoBundle\Services\Feds;

/* ==============================================================
 * Lines 25968    659 Scan csv file
 * Lookup  131  11328 Lookup person - vol - org
 * Person  125  12083 Update person dob,gender
 * Fed     131  11000 Update fed
 */
class VolsSync
{
    protected $conn;
    protected $results;
    
    // TODO: Bench mark prepared statements
    protected $selectFedStatement     = null;
    protected $updateFedStatement     = null;
    
    protected $selectFedCertStatement = null;
    protected $insertFedCertStatement = null;
    protected $updateFedCertStatement = null;
    
    protected $insertFedRegionStatement = null;
    protected $updateFedRegionStatement = null;
    
    protected $updatePersonStatement = null;
    
    /* ====================================================
     * TODO: Benchmark using pdo object
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->results = new VolsSyncResults();
        
        /* ========================================================
         * PersonFed - The select statement joins person and the region
         */
        $selectFedSql = <<<EOT
SELECT 
    fed.id                AS fed_id,
    fed.fed_role_date     AS fed_role_date,
    fed.fed_key           AS fed_key,
    fed.fed_key_verified  AS fed_key_verified,
          
    person.id     AS person_id,
    person.dob    AS person_dob,
    person.email  AS person_email,
    person.gender AS person_gender,
    
    fed.person_verified  AS person_verified,
                
    org.id               AS org_id,
    org.mem_year         AS org_mem_year,
    org.org_key          AS org_key,
    org.org_key_verified AS org_key_verified
                
FROM      person_feds     AS fed
LEFT JOIN persons         AS person ON person.id  = fed.person_id
LEFT JOIN person_fed_orgs AS org    ON org.fed_id = fed.id AND org.role = 'Region'
WHERE fed.fed_key = :fedKey
;
EOT;

        $this->selectFedStatement = $conn->prepare($selectFedSql);
        
        $updateFedSql = <<<EOT
UPDATE person_feds
SET 
    person_verified  = :personVerified,
    fed_key_verified = :fedKeyVerified,
    fed_role_date    = :fedRoleDate

WHERE id = :id;
EOT;
        
        $this->updateFedStatement = $conn->prepare($updateFedSql);
        
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
         * Person - Actually retrieved with join so select is not used
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
SET dob = :dob, gender = :gender
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
     * Update existing person gender and dob
     */
    protected function updatePerson($eaysoVol,$ceradVol)
    {
        // Needs to be linked
        $ceradPersonId = $ceradVol['person_id'];
        if (!$ceradPersonId) die('*** missing person id');
        
        // Two fields for update
        $ceradDOB    = $ceradVol['person_dob'];
        $ceradGender = $ceradVol['person_gender'];
        
        $eaysoDOB    = $this->processDate($eaysoVol['dob']);
        $eaysoGender = $eaysoVol['gender'];
        
        $params = array(
            'id'     => $ceradPersonId,
            'dob'    => $ceradDOB,
            'gender' => $ceradGender,
        );
                
        $needUpdate = false;
        
        if ((!$ceradDOB) && ($ceradDOB != $eaysoDOB)) 
        {
            $params['dob'] = $eaysoDOB;
            $needUpdate = true; 
        }
        if ((!$ceradGender) && ($ceradGender != $eaysoGender)) 
        {
            $params['gender'] = $eaysoGender;
            $needUpdate = true; 
        }
        if (!$needUpdate) return;
        
        $this->updatePersonStatement->execute($params);
        $this->results->countPersonUpdate++;
        
    }
    /* ==========================================================================
     * Mostly verification stuff
     */
    protected function updateFed($eaysoVol,$ceradVol)
    {
        // Needs to be linked
        $ceradFedId = $ceradVol['fed_id'];
        if (!$ceradFedId) die('*** missing vol id');
        
        // Two fields for update
        $ceradPersonVerified = $ceradVol['person_verified'];
        $ceradFedKeyVerified = $ceradVol['fed_key_verified'];
        $ceradFedRoleDate    = $ceradVol['fed_role_date'];
        
        $eaysoFedRoleDate = $this->processDate($eaysoVol['date_registered']);
        
        $params = array(
            'id'             => $ceradFedId,
            'personVerified' => $ceradPersonVerified,
            'fedKeyVerified' => $ceradFedKeyVerified,
            'fedRoleDate'    => $ceradFedRoleDate,
        );
                
        $needUpdate = false;
        
        /* =====================================================================
         * Trying to travk the earliest date that the person registered to volunteer in eayso
         */
        if ($eaysoFedRoleDate)
        {
            if ((!$ceradFedRoleDate) || ($ceradFedRoleDate > $eaysoFedRoleDate))
            {
                $params['fedRoleDate'] = $eaysoFedRoleDate;
                $needUpdate = true; 
            }
        }
        /* =====================================================================
         * If the person has not been verified then match fed emails with person email
         * To automatically verify
         */
        if ($ceradPersonVerified != 'Yes')
        {
            if ($ceradVol['person_email'] == $eaysoVol['email'])
            {
                $params['personVerified'] = 'Yes';
                $needUpdate = true;
            }
        }
        /* ===========================================================
         * Since we have a match, the fed_key itself if considered verified
         */
        if ($ceradFedKeyVerified != 'Yes')
        {
            $params['fedKeyVerified'] = 'Yes';
            $needUpdate = true; 
        }
        /* ===========================================================
         * Since we have a match, the fed_key itself if considered verified
         */
        if (!$needUpdate) return;
        
        $this->updateFedStatement->execute($params);
        $this->results->countFedUpdate++;
        
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
    /* ======================================================
     * Process line item
     */
    public function processItem($eaysoVol)
    {
        // Look up fed
        $eaysoFedKey = 'AYSOV' . $eaysoVol['fed_key'];
        $this->selectFedStatement->execute(array('fedKey' => $eaysoFedKey));   
        $rows = $this->selectFedStatement->fetchAll();
        if (count($rows) != 1) return;
        
        // Got it
        $ceradVol = $rows[0];
        
        $this->results->countFedMatch++;
        
        $this->updatePerson($eaysoVol,$ceradVol);
        $this->updateFed   ($eaysoVol,$ceradVol);
    }
    /* ==========================================================================
     * Main entry point
     * $params['filepath']
     * $params['basename']
     */
    public function process($params)
    {   
        // Param stuff
        $this->results->filepath = $params['filepath'];
        $this->results->basename = $params['basename'];
                
        // Open
        if (!is_readable($params['filepath']))
        {
            $this->results->message = '*** Could not open CSV file.';
            return $this->results;
        }
        $fp = fopen($params['filepath'],'rt');
        
        $headers = fgetcsv($fp);
        $indexes = $this->processHeaders($headers);
        
        if (!$indexes)
        {
            fclose($fp);
            $this->results->message = '*** Incorrect file headers.';
            return $this->results;
        }
        $this->processLines($fp,$indexes);
        
        fclose($fp);
        
        return $this->results;        
    }
    public function processLines($fp,$indexes)
    {
        while($line = fgetcsv($fp))
        {
            $this->results->countLines++;
            
            // Hack to get all the fields
            $item = $indexes;
            
            foreach($indexes as $key => $index)
            {
                if ($index !== null) $item[$key] = $line[$index];
            }
            $this->processItem($item);
        }
        return;
    }
    /* ========================================================
     * Returns an array of fields mapped to offset
     * If required fields are not found then return null
     */
    protected function processHeaders($headers)
    {
        $map = array(    
            'Membershipyear'    => 'mem_year',
            'Region'            => 'region',
            'AYSOID'            => 'fed_key',
            'FirstName'         => 'name_first',
            'LastName'          => 'name_last',
            'City'              => 'address_city',
            'State'             => 'address_state',
            'HomePhone'         => 'phone_home',
            'WorkPhone'         => 'phone_work',
            'CellPhone'         => 'phone_cell',
            'Email'             => 'email',
            'Gender'            => 'gender',
            'DOB'               => 'dob',
            'Changed Date'      => 'date_changed',
            'Registered Date'   => 'date_registered',
        );
        $index = array();
        foreach($map as $key)
        {
            $indexes[$key] = null;
        }
        foreach($headers as $index => $header)
        {
            if (isset($map[$header])) $indexes[$map[$header]] = $index;
        }
      //print_r($headers);
      //print_r($indexes);die();

        $missing = array();
        foreach(array('fed_key','mem_year','region') as $key)
        {
            if ($indexes[$key] === null) $missing = $key;
        }
        if (count($missing))
        {
            print_r($missing);
            die("*** MISSING\n");
            return null;
        }
        return $indexes;
    }
}
?>
