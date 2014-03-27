<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Persons;

use Symfony\Component\Yaml\Yaml;

class PersonsImport01YAMLResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $newPersonCount = 0;
    public $totalPersonCount = 0;
    public $existingPersonCount = 0;
    
    public function __toString()
    {
        return sprintf(
            "Imported %s\n" . 
            "Total    Persons: %d\n" .
            "New      Persons: %d\n" .
            "Existing Persons: %d\n",
                
            $this->basename,
            $this->totalPersonCount,
            $this->newPersonCount,
            $this->existingPersonCount
        );
    }
}
class PersonsImport01YAML
{
    protected $userRepo;
    protected $personRepo;
    
    public function __construct($personRepo,$userRepo)
    {
        $this->personRepo  = $personRepo;
        $this->userRepo    = $userRepo;
    }
    /* =================================================
     * Check all the fed keys and see if any match
     */
    protected function findPersonByFedKey($personx)
    {
        foreach($personx['feds'] as $fed)
        {
            $person = $this->personRepo->findOneByFedKey($fed['fedKey']);
            if ($person) return $person;
        }
        return null;
    }
    protected function processPersonNew($personx)
    {
        $this->results->newPersonCount++;
        
        $person = $this->personRepo->createPerson();
        
        $person->setGuid  ($personx['guid']);
        $person->setEmail ($personx['email']);
        $person->setPhone ($personx['phone']);
        $person->setGender($personx['gender']);
        
        $person->setNotes   ($personx['notes']);
        $person->setStatus  ($personx['status']);
        $person->setVerified($personx['verified']);
        
        if ($personx['dob'])
        {
            $dob = new \DateTime($personx['dob']);
            $person->setDob($dob);
        }
        $personName = $person->getName();
        $personName->full   = $personx['nameFull'];
        $personName->first  = $personx['nameFirst'];
        $personName->last   = $personx['nameLast'];
        $personName->nick   = $personx['nameNick'];
        $personName->middle = $personx['nameMiddle'];
        $person->setName($personName);
        
        $personAddress = $person->getAddress();
        $personAddress->city    = $personx['addressCity'];
        $personAddress->state   = $personx['addressState'];
        $personAddress->zipcode = $personx['addressZipcode'];
        $person->setAddress($personAddress);
        
        /* Now do the feds */
        foreach($personx['feds'] as $personFedx)
        {
            $personFed = $person->createFed();
            $personFed->setPersonVerified($personFedx['personVerified']);
            $personFed->setFed           ($personFedx['fed']);
            $personFed->setFedRole       ($personFedx['fedRole']);
            $personFed->setFedKey        ($personFedx['fedKey']);
            $personFed->setFedKeyVerified($personFedx['fedKeyVerified']);
            $personFed->setOrgKey        ($personFedx['orgKey']);
            $personFed->setOrgKeyVerified($personFedx['orgKeyVerified']);
            $personFed->setMemYear       ($personFedx['memYear']);
            $personFed->setStatus        ($personFedx['status']);
            
            if ($personFedx['fedRoleDate'])
            {
                $fedRoleDate = new \DateTime($personFedx['fedRoleDate']);
                $personFed->setFedRoleDate($fedRoleDate);
            }
            $person->addFed($personFed);
            
            // And the certs
            foreach($personFedx['certs'] as $certx)
            {
                $cert = $personFed->createCert();
                
                $roleDate  = $certx['roleDate']  ? new \DateTime($certx['roleDate'])  : null;
                $badgeDate = $certx['badgeDate'] ? new \DateTime($certx['badgeDate']) : null;
                
                $cert->setRole         ($certx['role']);
                $cert->setRoleDate     ($roleDate);
                $cert->setBadge        ($certx['badge']);
                $cert->setBadgeDate    ($badgeDate);
                $cert->setBadgeVerified($certx['badgeVerified']);
                $cert->setBadgeUser    ($certx['badgeUser']);
                $cert->setUpgrading    ($certx['upgrading']);
                $cert->setOrgKey       ($certx['orgKey']);
                $cert->setMemYear      ($certx['memYear']);
                $cert->setStatus       ($certx['status']);
                
                $personFed->addCert($cert);
            }
        }
        
        // Do the accounts
        foreach($personx['users'] as $userx)
        {
            // These are new so don't expecting existing accounts
            $userByGuid = $this->userRepo->findOneByPersonGuid($userx['personGuid']);
            if ($userByGuid)
            {
                echo sprintf("*** Have User for Guid %d\n",$userByGuid->getId());
                die();
            }
            // Might or might not have this
            $userByName = $this->userRepo->findOneBy(array('username' => $userx['username']));
            if ($userByName)
            {
                // On a clean database this is okay
                // Once we start adding persons then this picks up
                // Ignore for now
                echo sprintf("*** Have User for Name %s %s %s\n",
                        $userByName->getUsername(),
                        $userByName->getAccountName(),
                        $userx['personGuid']);
            }
            else
            {
                $user = $this->userRepo->createUser();
                
                $user->setPersonGuid     ($userx['personGuid']);
                $user->setPersonStatus   ($userx['personStatus']);
                $user->setPersonVerified ($userx['personVerified']);
              //$user->setPersonConfirmed($userx['personConfirmed']);
                
                $user->setUsername         ($userx['username']);
                $user->setUsernameCanonical($userx['usernameCanonical']);
                $user->setEmail            ($userx['email']);
                $user->setEmailCanonical   ($userx['emailCanonical']);
              //$user->setEmailConfirmed   ($userx['emailConfirmed']);
                
                $user->setSalt        ($userx['salt']);
                $user->setPassword    ($userx['password']);
                $user->setPasswordHint($userx['passwordHint']);
                $user->setAccountName ($userx['accountName']);
                
                $user->setRoles($userx['roles']);
               
                $this->userRepo->save($user);
            }
        }
        // Want to make this go away eventually
        $person->getPersonPersonPrimary();
        
        // Commit
        $this->personRepo->save($person);
        $this->personRepo->commit();
        $this->userRepo->commit();
        
      //echo sprintf("Added Person %d\n",$person->getId());
        
      //die();
    }
    /* ==================================================
     * Determines if have a new or existing person
     */
    protected function processPerson($personx)
    {
        $this->results->totalPersonCount++;
        
        // See if the person is already in the database
        $personGuid = $personx['guid'];
        $personExisting = $this->personRepo->findOneByGuid($personGuid);
        if ($personExisting)
        {
            print_r($personx); 
            echo "\n*** Already in database ***\n";
            die();
        }
        // See if fed id exists
        $personFedExisting = $this->findPersonByFedKey($personx);
        if ($personFedExisting)
        {
            $this->results->existingPersonCount++;
            return;
        }
        // Brand new person
        $this->processPersonNew($personx);
    }
    /* ==========================================================================
     * Main entry point
     * $params['filepath']
     * $params['basename']
     */
    public function process($params)
    {   
        $this->results = $results = new PersonsImport01YAMLResults();
        $results->filepath = $params['filepath'];
        $results->basename = $params['basename'];
        
        $data = Yaml::parse(file_get_contents($params['filepath']));
        $persons = $data['persons'];
        
        foreach($persons as $person)
        {
            $this->processPerson($person);
        }
        
        return $results;
        
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
