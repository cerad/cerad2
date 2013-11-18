<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Persons;

class Persons01ExportXML
{
    protected $conn;
    protected $writer;
    
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    /* ============================================================
     * Users (aka accounts) for a person
     */
    protected function processPersonUsers($conn,$writer,$personGuid)
    {
        $writer->startElement('person_users');
        
        $sql = "SELECT users.* FROM users WHERE person_guid = :personGuid;";
        
        $rows = $conn->fetchAll($sql,array('personGuid' => $personGuid));
        foreach($rows as $row)
        {
            $writer->startElement('person_user');
            
            $writer->writeAttribute('person_status',   $row['person_status']);
            $writer->writeAttribute('person_verified', $row['person_verified']);
            $writer->writeAttribute('person_confirmed',$row['person_confirmed']);
            
            $writer->writeAttribute('username',          $row['username']);
            $writer->writeAttribute('username_canonical',$row['username_canonical']);
            
            $writer->writeAttribute('email',          $row['email']);
            $writer->writeAttribute('email_canonical',$row['email_canonical']);
            $writer->writeAttribute('email_confirmed',$row['email_confirmed']);
            
            $writer->writeAttribute('salt',         $row['email']);
            $writer->writeAttribute('password',     $row['password']);
            $writer->writeAttribute('password_hint',$row['password_hint']);
            
            $roles = unserialize($row['roles']);
            $writer->writeAttribute('roles',implode(',',$roles));
            
            $writer->writeAttribute('account_name',         $row['account_name']);
            $writer->writeAttribute('account_enabled',      $row['account_enabled']);
            $writer->writeAttribute('account_locked',       $row['account_locked']);
            $writer->writeAttribute('account_expired',      $row['account_expired']);
            $writer->writeAttribute('account_expires_at',   $row['account_expires_at']);
            $writer->writeAttribute('account_created_on',   $row['account_created_on']);
            $writer->writeAttribute('account_updated_on',   $row['account_updated_on']);
            $writer->writeAttribute('account_last_login_on',$row['account_last_login_on']);
            
            $writer->writeAttribute('credentials_expired',  $row['credentials_expired']);
            $writer->writeAttribute('credentials_expire_at',$row['credentials_expire_at']);
             
            $writer->endElement();
        }
        $writer->endElement();
    }
    /* ============================================================
     * Links to other people
     */
    protected function processPersonPersons($conn,$writer,$personId)
    {
        $writer->startElement('person_persons');
        
        $sql = <<<EOT
SELECT person_persons.*, child.guid AS child_guid
FROM person_persons 
LEFT JOIN persons AS child ON child.id = person_persons.child_id
WHERE parent_id = :personId;
EOT;
        
        $rows = $conn->fetchAll($sql,array('personId' => $personId));
        foreach($rows as $row)
        {
            $writer->startElement('person_person');
            
            $writer->writeAttribute('role',       $row['role']);
          //$writer->writeAttribute('person_id',  $row['child_id']);
            $writer->writeAttribute('person_guid',$row['child_guid']);
            
            $writer->writeAttribute('status',  $row['status']);
            $writer->writeAttribute('verified',$row['verified']);
            
            $writer->endElement();
        }
        $writer->endElement();
    }
   /* ============================================================
     * Plans for a person
     */
    protected function processPersonPlans($conn,$writer,$personId)
    {
        $writer->startElement('person_plans');
        
        $sql = "SELECT person_plans.* FROM person_plans WHERE person_id = :personId;";
        
        $rows = $conn->fetchAll($sql,array('personId' => $personId));
        foreach($rows as $row)
        {
            $writer->startElement('person_plan');
            
            $writer->writeAttribute('project_id',$row['project_id']);
            
            $writer->writeAttribute('notes',   $row['notes']);
            $writer->writeAttribute('status',  $row['status']);
            $writer->writeAttribute('verified',$row['verified']);
            
            /* ===============================================
             * Unpack basic plans
             */
            $writer->startElement('person_plan_basic');
            
            $basic = unserialize($row['basic']);
            
            $writer->writeAttribute('willAttend', $basic['attending']);
            $writer->writeAttribute('willReferee',$basic['refereeing']);
            $writer->writeAttribute('willMentor', $basic['willMentor']);
            $writer->writeAttribute('wantMentor', $basic['wantMentor']);
            
            $writer->writeAttribute('willCoach',    $basic['coaching']);
            $writer->writeAttribute('willVolunteer',$basic['volunteering']);
            $writer->writeAttribute('willPlay',     $basic['playing']);
            
            $writer->writeAttribute('shirtSize',$basic['tshirt']);
            $writer->writeAttribute('notes',    $basic['notes']);

            $writer->endElement(); // person_plan_basic
            
            // Unpack availability
            // Unpack levels
            
            // Done
            $writer->endElement();
        }
        $writer->endElement();
    }
    /* =================================================================
     * Organizations for a person fed
     */
    protected function processPersonFedOrgs($conn,$writer,$personFedId)
    {
        $writer->startElement('person_fed_orgs');
        
        $sql = "SELECT person_fed_orgs.* FROM person_fed_orgs WHERE fed_id = :personFedId;";
        
        $rows = $conn->fetchAll($sql,array('personFedId' => $personFedId));
        foreach($rows as $row)
        {
            $writer->startElement('person_fed_org');
            
            $writer->writeAttribute('role',     $row['role']);
            $writer->writeAttribute('org_id',   $row['org_id']);
            
            $writer->writeAttribute('mem_year',   $row['mem_year']);
            $writer->writeAttribute('mem_last',   $row['mem_last']);
            $writer->writeAttribute('mem_first',  $row['mem_first']);
            $writer->writeAttribute('mem_expires',$row['mem_expires']);
            
            $writer->writeAttribute('bc_year',   $row['bc_year']);
            $writer->writeAttribute('bc_last',   $row['bc_last']);
            $writer->writeAttribute('bc_first',  $row['bc_first']);
            $writer->writeAttribute('bc_expires',$row['bc_expires']);
            
            $writer->writeAttribute('status',   $row['status']);
            $writer->writeAttribute('verified', $row['verified']);
            
            $writer->endElement();
        }
        $writer->endElement();
    }
    /* =================================================================
     * Certs for a person fed
     */
    protected function processPersonFedCerts($conn,$writer,$personFedId)
    {
        $writer->startElement('person_fed_certs');
        
        $sql = "SELECT person_fed_cert.* FROM person_fed_certs AS person_fed_cert WHERE fed_id = :personFedId;";
        
        $rows = $conn->fetchAll($sql,array('personFedId' => $personFedId));
        foreach($rows as $row)
        {
            $writer->startElement('person_fed_cert');
            
            $writer->writeAttribute('role',     $row['role']);
            $writer->writeAttribute('badge',    $row['badge']);
            $writer->writeAttribute('badgex',   $row['badgex']);
            
            $writer->writeAttribute('date_cert',    $row['date_cert']);
            $writer->writeAttribute('date_upgraded',$row['date_upgraded']);
            $writer->writeAttribute('date_expires', $row['date_expires']);
            
            $writer->writeAttribute('upgrading',$row['upgrading']);
            $writer->writeAttribute('status',   $row['status']);
            $writer->writeAttribute('verified', $row['verified']);
            
            $writer->endElement();
        }
        $writer->endElement(); // PersonFeds
    }
    protected function processPersonFeds($conn,$writer,$personId)
    {
        $writer->startElement('person_feds');
        
        $sql = "SELECT person_fed.* FROM person_feds AS person_fed WHERE person_id = :personId;";
        
        $rows = $conn->fetchAll($sql,array('personId' => $personId));
        foreach($rows as $row)
        {
            $writer->startElement('person_fed');
            
            $writer->writeAttribute('fed_id',     $row['id']);
            $writer->writeAttribute('fed_role_id',$row['fed_role_id']);
            $writer->writeAttribute('status',     $row['status']);
            $writer->writeAttribute('verified',   $row['verified']);

            $this->processPersonFedCerts($conn,$writer,$row['id']);
            $this->processPersonFedOrgs ($conn,$writer,$row['id']);
            
            $writer->endElement();
        }
        $writer->endElement(); // PersonFeds
    }
    /* ============================================================
     * Write individual person
     */
    protected function processPerson($conn,$writer,$person)
    {
        $personId = $person['id'];
        if ($personId > 5) return;
        
        $writer->startElement('person');
            
      //$writer->writeAttribute('id',  $person['id']);
        $writer->writeAttribute('guid',$person['guid']);
        
        $writer->writeAttribute('name_full',  $person['name_full']);
        $writer->writeAttribute('name_first', $person['name_first']);
        $writer->writeAttribute('name_last',  $person['name_last']);
        $writer->writeAttribute('name_nick',  $person['name_nick']);
        $writer->writeAttribute('name_middle',$person['name_middle']);
        
        $writer->writeAttribute('email', $person['email']);
        $writer->writeAttribute('phone', $person['phone']);
        $writer->writeAttribute('gender',$person['gender']);
        $writer->writeAttribute('dob',   $person['dob']);
        
        $writer->writeAttribute('address_city',   $person['address_city']);
        $writer->writeAttribute('address_state',  $person['address_state']);
        $writer->writeAttribute('address_zipcode',$person['address_zipcode']);
        
        $writer->writeAttribute('notes',   $person['notes']);
        $writer->writeAttribute('status',  $person['status']);
        $writer->writeAttribute('verified',$person['verified']);
           
        $this->processPersonFeds   ($conn,$writer,$person['id']);
        $this->processPersonPlans  ($conn,$writer,$person['id']);
        $this->processPersonPersons($conn,$writer,$person['id']);
        $this->processPersonUsers  ($conn,$writer,$person['guid']);
        
        $writer->endElement(); // Person
    }
    /* =======================================================
     * Person Collection
     */
    protected function processPersons($conn,$writer)
    {
        $writer->startElement('persons');

        $sql = "SELECT person.* FROM persons AS person ORDER BY person.id;";
        
        $rows = $conn->fetchAll($sql);
        
        foreach($rows as $row)
        {
            $this->processPerson($conn,$writer,$row);
          //print_r($row); die();
        }
        
        $writer->endElement(); // Persons
    }
    /* ==========================================================================
     * Main entry point
     */
    public function process()
    {
        $this->writer = $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        
        $writer->startElement('export');
        
        $writer->writeAttribute('name','S5Games');
        
        $this->processPersons($this->conn,$writer);
        
        $writer->endElement(); // Export

        $writer->endDocument();
      //echo $writer->outputMemory(true); 
        
        return $writer;
    }
}
?>
