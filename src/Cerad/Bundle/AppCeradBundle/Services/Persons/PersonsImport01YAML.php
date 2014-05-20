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
        
        if (isset($personx['notes']))    $person->setNotes($personx['notes']);
        if (isset($personx['status']))   $person->setStatus  ($personx['status']);
        if (isset($personx['verified'])) $person->setVerified($personx['verified']);
        
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
        
        if (isset($personx['nameMiddle'])) $personName->middle = $personx['nameMiddle'];
        
        $person->setName($personName);
        
        $personAddress = $person->getAddress();
        $personAddress->city    = $personx['addressCity'];
        $personAddress->state   = $personx['addressState'];
        
        if (isset($personx['addressZipCode'])) $personAddress->zipcode = $personx['addressZipcode'];
        
        $person->setAddress($personAddress);
        
        /* Now do the feds */
        foreach($personx['feds'] as $personFedx)
        {
            $personFed = $person->createFed();
            
            if (isset($personFedx['personVerified']))
                $personFed->setPersonVerified($personFedx['personVerified']);
            
            $personFed->setFed    ($personFedx['fed']);
            $personFed->setFedRole($personFedx['fedRole']);
            $personFed->setFedKey ($personFedx['fedKey']);
            $personFed->setOrgKey ($personFedx['orgKey']);
            
            if (isset($personFedx['fedKeyVerified']))
                $personFed->setFedKeyVerified($personFedx['fedKeyVerified']);
            
            if (isset($personFedx['orgKeyVerified']))    
                $personFed->setOrgKeyVerified($personFedx['orgKeyVerified']);
            
            $personFed->setMemYear($personFedx['memYear']);
            $personFed->setStatus ($personFedx['status']);
            
            if (isset($personFedx['fedRoleDate']) && $personFedx['fedRoleDate'])
            {
                $fedRoleDate = new \DateTime($personFedx['fedRoleDate']);
                $personFed->setFedRoleDate($fedRoleDate);
            }
            $person->addFed($personFed);
            
            // And the certs
            foreach($personFedx['certs'] as $certx)
            {
                $cert = $personFed->createCert();
                
                $cert->setRole ($certx['role']);
                $cert->setBadge($certx['badge']);
                
                if (isset($certx['roleDate']) && $certx['roleDate'])
                {
                    $roleDate  = new \DateTime($certx['roleDate']);
                    $cert->setRoleDate($roleDate);
                }
                if (isset($certx['badgeDate']) && $certx['badgeDate'])
                {
                    $badgeDate  = new \DateTime($certx['badgeDate']);
                    $cert->setBadgeDate($badgeDate);
                }
                if (isset($certx['badgeVerified']))
                    $cert->setBadgeVerified($certx['badgeVerified']);
                
                if (isset($certx['badgeUser']))
                    $cert->setBadgeUser($certx['badgeUser']);
                
                if (isset($certx['upgrading']))
                    $cert->setUpgrading($certx['upgrading']);
                
                if (isset($certx['orgKey']))
                    $cert->setOrgKey($certx['orgKey']);
                
                if (isset($certx['memYear']))
                    $cert->setMemYear($certx['memYear']);
                
                if (isset($certx['status']))
                    $cert->setStatus($certx['status']);
                
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
                
                $user->setPersonGuid($userx['personGuid']);
                
                if (isset($userx['personStatus']))
                    $user->setPersonStatus($userx['personStatus']);
                
                if (isset($userx['personVerified']))
                    $user->setPersonVerified($userx['personVerified']);
              
                $user->setUsername         ($userx['username']);
                $user->setUsernameCanonical($userx['usernameCanonical']);
                $user->setEmail            ($userx['email']);
                $user->setEmailCanonical   ($userx['emailCanonical']);
                
                $user->setSalt        ($userx['salt']);
                $user->setPassword    ($userx['password']);
                
                if (isset($userx['passwordHint']))
                    $user->setPasswordHint($userx['passwordHint']);
                
                $user->setAccountName($userx['accountName']);
                
                $user->setRoles($userx['roles']);
               
                $this->userRepo->save($user);
                
                // Authens 
                foreach($userx['authens'] as $authenx)
                {
                    $authen = $user->createAuthen();
                    
                    $authen->setId     ($authenx['identifier']);
                    $authen->setSource ($authenx['source']);
                    $authen->setStatus ($authenx['status']);
                    $authen->setProfile($authenx['profile']);
                    
                    $user->addAuthen($authen);
                }
            }
        }
        // Want to make this go away eventually
        $person->getPersonPersonPrimary();
        
        // Commit
        $this->personRepo->save($person);
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
        
        $persons = Yaml::parse(file_get_contents($params['filepath']));
        
        foreach($persons as $person)
        {
            $this->processPerson($person);
        }
        $this->personRepo->commit();
        $this->userRepo->commit();
        
        return $results;
        
    }
}
?>
