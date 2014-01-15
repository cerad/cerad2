<?php
namespace Cerad\Bundle\EaysoBundle\Services\Feds;

/* ==============================================================
 * Lines 25968    659 Scan csv file
 * Lookup  131  11328 Lookup person - vol - org
 * Person  125  12083 Update person dob,gender
 * Fed     131  11000 Update fed
 */
class FedsSync
{
    protected $conn;
    protected $results;
    protected $certRepo;
    
    /* ====================================================
     * TODO: Benchmark using pdo object
     */
    public function __construct($conn,$certRepo)
    {
        $this->conn     = $conn;
        $this->certRepo = $certRepo;
        $this->results  = new FedsSyncResults();
        
        $this->prepareFedSelect($conn);
        $this->prepareFedUpdate($conn);
        
        $this->prepareCertSelect($conn);
        $this->prepareCertInsert($conn);
        $this->prepareCertUpdate($conn);
        
        $this->preparePersonUpdate($conn);
        
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
    protected $statementPersonUpdate;
    
    protected function preparePersonUpdate($conn)
    {
        $sql = <<<EOT
UPDATE persons 
SET dob = :dob, gender = :gender
WHERE id = :id
;
EOT;
        $this->statementPersonUpdate = $conn->prepare($sql);
    }
    protected function updatePerson($eaysoFed,$ceradFed)
    {
        // Needs to be linked
        $ceradPersonId = $ceradFed['person_id'];
        if (!$ceradPersonId) die('*** missing person id');
        
        // Two fields for update
        $ceradDOB    = $ceradFed['person_dob'];
        $ceradGender = $ceradFed['person_gender'];
        
        $eaysoDOB    = $this->processDate($eaysoFed['dob']);
        $eaysoGender = $eaysoFed['gender'];
        
        $params = array(
            'id'     => $ceradPersonId,
            'dob'    => $ceradDOB,
            'gender' => $ceradGender,
        );
                
        $needUpdate = false;
 
        if ((!$ceradDOB) && ($eaysoDOB)) 
        {
            $params['dob'] = $eaysoDOB;
            $needUpdate = true; 
        }
        if ((!$ceradGender) && ($eaysoGender)) 
        {
            $params['gender'] = $eaysoGender;
            $needUpdate = true; 
        }
        if (!$needUpdate) return;
        
        $this->statementPersonUpdate->execute($params);
        $this->results->countPersonUpdate++;
    }
    /* ==========================================================================
     * Mostly verification stuff
     */
    protected $statementFedUpdate;
    
    protected function prepareFedUpdate($conn)
    {
        $sql = <<<EOT
UPDATE person_feds
SET 
    person_verified  = :person_verified,
    fed_key_verified = :fed_key_verified,
    fed_role_date    = :fed_role_date,
    org_key          = :org_key,
    org_key_verified = :org_key_verified,
    mem_year         = :mem_year

WHERE id = :id;
EOT;
        
        $this->statementFedUpdate = $conn->prepare($sql);
        
    }
    protected function updateFed($eaysoFed,$ceradFed)
    {
        // Needs to be linked, should never fail
        $ceradPersonFedId = $ceradFed['id'];
        if (!$ceradPersonFedId) die('*** missing fed id');
        
        // Two fields for update
        $ceradPersonEmail    = $ceradFed['person_email'];
        $ceradPersonVerified = $ceradFed['person_verified'];
        $ceradFedKeyVerified = $ceradFed['fed_key_verified'];
        $ceradFedRoleDate    = $ceradFed['fed_role_date'];
        
        $ceradOrgKey         = $ceradFed['org_key'];
        $ceradOrgKeyVerified = $ceradFed['org_key_verified'];
        $ceradMemYear        = $ceradFed['mem_year'];
        
        $eaysoPersonEmail = $eaysoFed['email'];
        $eaysoFedRoleDate = $this->processDate($eaysoFed['date_registered']);
        
        $eaysoOrgKey = (int)$eaysoFed['region'];
        if ($eaysoOrgKey) $eaysoOrgKey = sprintf('AYSOR%04u',$eaysoOrgKey);
        
        $eaysoMemYear = $eaysoFed['mem_year'];
        
        $params = array(
            'id'               => $ceradPersonFedId,
            'person_verified'  => $ceradPersonVerified,
            'fed_key_verified' => $ceradFedKeyVerified,
            'fed_role_date'    => $ceradFedRoleDate,
            'org_key'          => $ceradOrgKey,
            'org_key_verified' => $ceradOrgKeyVerified,
            'mem_year'         => $ceradMemYear,
        );
        
        $needUpdate = false;
        
        /* =====================================================================
         * Trying to travk the earliest date that the person registered to volunteer in eayso
         */
        if ($eaysoFedRoleDate)
        {
            if ((!$ceradFedRoleDate) || ($ceradFedRoleDate > $eaysoFedRoleDate))
            {
                $params['fed_role_date'] = $eaysoFedRoleDate;
                $needUpdate = true; 
            }
        }
        /* =====================================================================
         * If the person has not been verified then match fed emails with person email
         * To automatically verify
         */
        if ($ceradPersonVerified != 'Yes')
        {
            if ($ceradPersonEmail && ($ceradPersonEmail == $eaysoPersonEmail))
            {
                $params['person_verified'] = 'Yes';
                $needUpdate = true;
            }
        }
        /* ===========================================================
         * Since we have a match, the fed_key itself if considered verified
         */
        if ($ceradFedKeyVerified != 'Yes')
        {
            $params['fed_key_verified'] = 'Yes';
            $needUpdate = true; 
        }
        /* ==========================
         * Update org_key is blank
         */
        if (!$ceradOrgKey && $eaysoOrgKey) 
        {
            $params['org_key'] = $eaysoOrgKey;
            $params['org_key_verified'] = 'Yes';
            $needUpdate = true;
        }
        /* ============================================
         * If I do ahve a org key see if they match
         */
        if ($ceradOrgKey && ($ceradOrgKey == $eaysoOrgKey))
        {
            if ($ceradOrgKeyVerified != 'Yes')
            {
                $params['org_key_verified'] = 'Yes';
                $needUpdate = true;
            }
        }
        /* ===========================================================
         * Keep mem year at highest value
         */
        if ($eaysoMemYear)
        {
            if ((!$ceradMemYear) || ($ceradMemYear < $eaysoMemYear))
            {
                $params['mem_year'] = $eaysoMemYear;
                $needUpdate = true; 
            }
        }
        if (!$needUpdate) return;
        
        $this->statementFedUpdate->execute($params);
        $this->results->countFedUpdate++;
    }
    /* ==========================================================================
     * Process cert information
     */
    protected $statementCertSelect;
    protected $statementCertInsert;
    protected $statementCertUpdate;
    
    protected function prepareCertSelect($conn)
    {
        $sql = <<<EOT
SELECT 
    cert.id              AS id,
    cert.role_date       AS role_date,
    cert.badge           AS badge,
    cert.badge_date      AS badge_date,
    cert.badge_verified  AS badge_verified,
    cert.mem_year        AS mem_year
                
FROM person_fed_certs AS cert
WHERE cert.person_fed_id = :person_fed_id AND cert.role = :role
;
EOT;
        $this->statementCertSelect = $conn->prepare($sql);
    }
    protected function prepareCertInsert($conn)
    {
        $sql = <<<EOT
INSERT INTO person_fed_certs 
    ( person_fed_id, role, role_date, badge, badge_date, badge_verified, mem_year, status)
VALUES
    (:person_fed_id,:role,:role_date,:badge,:badge_date,'Yes',          :mem_year,'Active')             
;
EOT;
        $this->statementCertInsert = $conn->prepare($sql);
    }
    protected function prepareCertUpdate($conn)
    {
        $sql = <<<EOT
UPDATE person_fed_certs
SET 
    role_date      = :role_date,          
    badge          = :badge,
    badge_date     = :badge_date,
    badge_verified = :badge_verified,
    mem_year       = :mem_year

WHERE id = :id;    
;
EOT;
        $this->statementCertUpdate = $conn->prepare($sql);
    }
    protected function processCert($eaysoFed,$ceradFed)
    {
        // Couple of things
        $eaysoCertRole  = $eaysoFed['cert_role'];
        if ($eaysoCertRole == 'Other') return;

        $eaysoCertBadge   = $eaysoFed['cert_badge'];
        $eaysoCertDate    = $this->processDate($eaysoFed['cert_date']);
        $eaysoCertMemYear = $eaysoFed['mem_year'];
        
        $ceradPersonFedId = $ceradFed['id'];
        
        // Do we have a cert record?
        $paramsSelect = array(
            'person_fed_id' => $ceradPersonFedId,
            'role'          => $eaysoCertRole,
        );
        $this->statementCertSelect->execute($paramsSelect);   
        $rows = $this->statementCertSelect->fetchAll();
        
        if (count($rows) == 0)
        {
            // Insert new record
            $paramsInsert = array
            (
                'person_fed_id' => $ceradPersonFedId, 
                'role'          => $eaysoCertRole, 
                'role_date'     => $eaysoCertDate, 
                'badge'         => $eaysoCertBadge, 
                'badge_date'    => $eaysoCertDate,
                'mem_year'      => $eaysoCertMemYear,   
            );
            $this->statementCertInsert->execute($paramsInsert);
            $this->results->countCertInsert++;
            return;
        }
        // Should never happen
        if (count($rows) > 1)
        {
            die("*** multiple cert records for role ***");
        }
        // Update existing record
        $ceradCert = $rows[0];
        
        $ceradCertRoleDate      = $ceradCert['role_date'];
        $ceradCertBadge         = $ceradCert['badge'];
        $ceradCertBadgeDate     = $ceradCert['badge_date'];
        $ceradCertBadgeVerified = $ceradCert['badge_verified'];
        $ceradCertMemYear       = $ceradCert['mem_year'];
        
        $params = array
        (
            'id'             => $ceradCert['id'],
            'role_date'      => $ceradCertRoleDate,
            'badge'          => $ceradCertBadge,
            'badge_date'     => $ceradCertBadgeDate,
            'badge_verified' => $ceradCertBadgeVerified,
            'mem_year'       => $ceradCertMemYear,
        );
        $needUpdate = false;
        
        // Use cert date for two possible updates
        if ($eaysoCertDate)
        {
            // Set if currently empty
            if (!$ceradCertBadgeDate)
            {
                $params['badge_date'] = $eaysoCertDate;
                $needUpdate = true;
            }
            // Store earliest role date
            if ((!$ceradCertRoleDate) || ($eaysoCertDate < $ceradCertRoleDate))
            {
                $params['role_date'] = $eaysoCertDate;
                $needUpdate = true;
            }
        }
        // Update mem year if later
        if ($eaysoCertMemYear)
        {
            if ((!$ceradCertMemYear) || ($eaysoCertMemYear > $ceradCertMemYear))
            {
                $params['mem_year'] = $eaysoCertMemYear;
                $needUpdate = true;
            }
        }
        // Process badge, should always have eayso and cerad
        if ($ceradCertBadge == $eaysoCertBadge)
        {
            if ($ceradCertBadgeVerified != 'Yes')
            {
                $params['badge_verified'] = 'Yes';
                $needUpdate = true;
            }
        }
        else
        {
            // Compare badges
            if ($this->certRepo->compareBadges($eaysoCertRole,$eaysoCertBadge,$ceradCertBadge) > 0)
            {
                $params['badge']          = $eaysoCertBadge;
                $params['badge_verified'] = 'Yes';
                $needUpdate = true;
            }
        }
        // Update if needed
        if (!$needUpdate) return;
        
        $this->statementCertUpdate->execute($params);
        $this->results->countCertUpdate++;
    }
    /* ======================================================
     * Process Fed
     */
    protected $statementFedSelect;
    
    protected function prepareFedSelect($conn)
    {
        $sql = <<<EOT
SELECT 
    fed.id                AS id,
    fed.fed_role          AS fed_role,
    fed.fed_role_date     AS fed_role_date,
    fed.fed_key           AS fed_key,
    fed.fed_key_verified  AS fed_key_verified,

    fed.org_key          AS org_key,
    fed.org_key_verified AS org_key_verified,
    fed.mem_year         AS mem_year,
          
    person.id     AS person_id,
    person.dob    AS person_dob,
    person.email  AS person_email,
    person.gender AS person_gender,
    
    fed.person_verified  AS person_verified
                
FROM      person_feds     AS fed
LEFT JOIN persons         AS person ON person.id  = fed.person_id
WHERE fed.fed_key = :fed_key
;
EOT;

        $this->statementFedSelect = $conn->prepare($sql);
    }
    public function processFed($eaysoFed)
    {
        // Ignore old records completely
        if (substr($eaysoFed['mem_year'],0,2) != 'MY') return;
 
        // Look up fed
        $eaysoFedKey = 'AYSOV' . $eaysoFed['fed_key'];
        
        /* =============================================================
         * Merely exicuting this staements causes memory usage to jimp from 7 to 77MB
         * Why?  Research later, maybe try pdo?
         */
        $this->statementFedSelect->execute(array('fed_key' => $eaysoFedKey)); 
        $rows = $this->statementFedSelect->fetchAll(); 

        if (count($rows) != 1) return;
        
        // Got it
        $ceradFed = $rows[0];
       
        $this->results->countFedMatch++;
        
        $this->updateFed   ($eaysoFed,$ceradFed);
        $this->updatePerson($eaysoFed,$ceradFed);
        
        // See if it a cert
        $eaysoCertDesc = $eaysoFed['cert_desc'];
        if (!$eaysoCertDesc) return;
        
        // Lookup badges
        $eaysoCerts = $this->certRepo->findByCertDesc($eaysoCertDesc);
        foreach($eaysoCerts as $eaysoCertRole => $eaysoCertBadge)
        {
            $eaysoFed['cert_role' ] = $eaysoCertRole;
            $eaysoFed['cert_badge'] = $eaysoCertBadge;
            
            $this->processCert($eaysoFed,$ceradFed);
        }
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
        $this->processRows($fp,$indexes);
        
        fclose($fp);
        
        return $this->results;        
    }
    public function processRows($fp,$indexes)
    {
        while($row = fgetcsv($fp))
        {
            $this->results->countRows++;
            
            // Hack to get all the fields
            $fed = $indexes;
            
            foreach($indexes as $key => $index)
            {
                if ($index !== null) $fed[$key] = trim($row[$index]);
            }
            $this->processFed($fed);
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
            'Membership Year'   => 'mem_year',
            'Membershipyear'    => 'mem_year',
            'Region'            => 'region',
            'RegionNumber'      => 'region',
            'AYSOID'            => 'fed_key',
          //'Name'              => 'name_full',
          //'FirstName'         => 'name_first',
          //'LastName'          => 'name_last',
          //'City'              => 'address_city',
          //'State'             => 'address_state',
          //'HomePhone'         => 'phone_home',
          //'WorkPhone'         => 'phone_work',
          //'BusinessPhone'     => 'phone_work',
          //'CellPhone'         => 'phone_cell',
            'Email'             => 'email',
            'Gender'            => 'gender',
            'DOB'               => 'dob',
          //'Changed Date'      => 'date_changed',
            'Registered Date'   => 'date_registered',
            'CertificationDesc' => 'cert_desc',
            'CertDate'          => 'cert_date',
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
