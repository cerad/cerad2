<?php
namespace Cerad\Bundle\PersonBundle\Model;

use Cerad\Bundle\PersonBundle\Model\BaseModel;

use Cerad\Bundle\PersonBundle\Model\Person;

use Cerad\Bundle\PersonBundle\Model\PersonFedOrg;
use Cerad\Bundle\PersonBundle\Model\PersonFedCert;

/* ================================================
 * This is really a link to a FedPersonType but
 * works fine in the person context
 */
class PersonFed extends BaseModel
{   
    const FedAYSO = 'AYSO';
    const FedUSSF = 'USSF';
    const FedNFHS = 'NFHS';
    
    const FedRoleAYSOV = 'AYSOV'; // Volunteer
    const FedRoleAYSOP = 'AYSOP'; // Player
    const FedRoleUSSFC = 'USSFC'; // Contractor
    const FedRoleNFHSC = 'NFHSC'; // Contractor
    
    protected $id;
    
    protected $fed;         // Not currently used
    protected $fedRole;
    protected $fedRoleDate; // First joined the federation
    
    protected $fedKey;          // AYSOV12341234 Globally Unique
    protected $fedKeyVerified;  // personFedKey or fedPersonKey
    
    protected $person;          // personModelId
    protected $personVerified;
    
    protected $status = 'Active';
    
    protected $orgs;
    protected $certs;
    
    public function __construct()
    {
        $this->orgs  = array();
        $this->certs = array();
    }
    public function getId         () { return $this->id;          }
    public function getFed        () { return $this->fed;         }
    public function getFedRole    () { return $this->fedRole;     }
    public function getFedRoleDate() { return $this->fedRoleDate; }
    
    public function getFedKey         () { return $this->fedKey;    }
    public function getFedKeyVerified () { return $this->fedKeyVerified; }
    
    public function getPerson         () { return $this->person;    }
    public function getPersonVerified () { return $this->personVerified; }
    
    public function getStatus   () { return $this->status;    }
    
    public function setId         ($value) { $this->onPropertySet('id',         $value); }
    public function setFed        ($value) { $this->onPropertySet('fed',        $value); }
    public function setFedRole    ($value) { $this->onPropertySet('fedRole',    $value); }
    public function setFedRoleDate($value) { $this->onPropertySet('fedRoleDate',$value); }
    
    public function setFedKey        ($value) { $this->onPropertySet('fedKey',        $value); }
    public function setFedKeyVerified($value) { $this->onPropertySet('fedKeyVerified',$value); }
    
    public function setPerson(Person $person) { $this->onPropertySet('person',        $person); }
    public function setPersonVerified($value) { $this->onPropertySet('personVerified',$value);  }
    
    public function setStatus($value) { $this->onPropertySet('status',$value); }
    
    /* ====================================================
     * Certification
     */
    public function createCert($params) { return new PersonFedCert($params); }
    
    public function getCerts() { return $this->certs; }
    
    public function removeCert(PersonFedCert $cert)
    {
        $role = $cert->getRole();
         
        if (!isset($this->certs[$role])) return;
        
        unset($this->certs[$role]);
        
        $this->onPropertyChanged('certs');
    }
    
    public function addCert(PersonFedCert $cert)
    {
        $role = $cert->getRole();
        
        if (isset($this->certs[$role])) return;
        
        $this->certs[$role] = $cert;
         
        $cert->setFed($this);
        
        $this->onPropertyChanged('certs');
    }
    public function getCert($role, $autoCreate = true)
    {
        if (isset($this->certs[$role])) { return $this->certs[$role]; }
        
        if (!$autoCreate) return null;
        
        $cert = $this->createCert();
        $cert->setRole($role);
        $this->addCert($cert);
        return $cert;
    }
    public function getCertReferee($autoCreate = true)
    {
        return $this->getCert(PersonFedCert::RoleReferee,$autoCreate);
    }
    public function getCertSafeHaven($autoCreate = true)
    {
        return $this->getCert(PersonFedCert::RoleSafeHaven,$autoCreate);
    }
    
    /* ====================================================
     * Organizations
     */
    public function createOrg($params) { return new PersonFedOrg($params); }
    
    public function getOrgs() { return $this->orgs; }
 
    public function removeOrg(PersonFedOrg $org)
    {
        $role = $org->getRole();
         
        if (!isset($this->orgs[$role])) return;
        
        unset($this->orgs[$role]);
        
        $this->onPropertyChanged('orgs');
    }
    public function addOrg(PersonFedOrg $org)
    {
        $role = $org->getRole();
        
        if (isset($this->orgs[$role])) return;
 
        $this->orgs[$role] = $org;
        
        $org->setFed($this);
        
        $this->onPropertyChanged('orgs');
    }
    public function getOrg($role = null, $autoCreate = true)
    {
        // Default role based on Fed Role
        if ($role == null)
        {
            switch($this->fedRoleId)
            {
                case self::FedRoleAYSOV: $role = PersonFedOrg::RoleRegion; break;
                case self::FedRoleUSSFC: $role = PersonFedOrg::RoleState;  break;
                default: throw new \Exception('No role for personFed.findOrg');
            }
        }
        if (isset($this->orgs[$role])) return $this->orgs[$role];
 
        if (!$autoCreate) return null;
        
        $org = $this->createOrg();
        $org->setRole($role);
        $this->addOrg($org);
        return $org;
    }
    public function getOrgState($autoCreate = true)
    {
        return $this->getOrg(PersonFedOrg::RoleState,$autoCreate);
    }
    public function getOrgRegion($autoCreate = true)
    {
        return $this->getOrg(PersonFedOrg::RoleRegion,$autoCreate);
    }
}
?>
