<?php
namespace Cerad\Bundle\UserBundle\Model;

use Cerad\Bundle\UserBundle\Model\UserInterface;

class User extends BaseModel implements UserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';
    
    protected $salt;
    protected $email;
    protected $emailCanonical;
    protected $username;
    protected $usernameCanonical;
    protected $password;
    protected $passwordPlain;
    protected $confirmationToken;
    
    protected $roles = array();
    
    // Wants to be a value object
    protected $personId;
    protected $personName;
    protected $personStatus   = 'Active';
    protected $personVerified = 'No';
    
    protected $identifiers; // User Identifiers
    
    protected $enabled            = true;  // UserEnables
    protected $accountLocked      = false;
    protected $accountExpired     = false;
    protected $accountExpiresAt;    // DateTime
    protected $credentialsExpired  = false;
    protected $credentialsExpireAt; // DateTime
    
    protected $passwordRequestedAt;
    protected $passwordRequestExpired; // bool
    
    protected $loginLast;
    
    public function __construct()
    {
        $this->id   = $this->genId();
        
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        
        $this->identifiers = array();
        
    }
    /* =====================================================
     * Basic account getter/setters
     */
    public function getId()                { return $this->id;                }
    public function getSalt()              { return $this->salt;              }
    public function getEmail()             { return $this->email;             }
    public function getEmailCanonical()    { return $this->emailCanonical;    }
    public function getUsername()          { return $this->username;          }
    public function getUsernameCanonical() { return $this->usernameCanonical; }
    public function getPassword()          { return $this->password;          }
    public function getPlainPassword()     { return $this->passwordPlain;     }
    public function getConfirmationToken() { return $this->confirmationToken; }
    
    public function setId               ($value) { $this->onPropertySet('id',               $value); }
    public function setSalt             ($value) { $this->onPropertySet('salt',             $value); }
    public function setEmail            ($value) { $this->onPropertySet('email',            $value); }
    public function setEmailCanonical   ($value) { $this->onPropertySet('emailCanonical',   $value); }
    public function setUsername         ($value) { $this->onPropertySet('username',         $value); }
    public function setUsernameCanonical($value) { $this->onPropertySet('usernameCanonical',$value); }
    public function setPassword         ($value) { $this->onPropertySet('password',         $value); }
    public function setPlainPassword    ($value) { $this->onPropertySet('passwordPlain',    $value); }
    public function setConfirmationToken($value) { $this->onPropertySet('confirmationToken',$value); }
    
    /* =======================================================
     * My person link
     */
    public function getName()           { return $this->personName;     }
    public function getPersonId()       { return $this->personId;       }
    public function getPersonName()     { return $this->personName;     }
    public function getPersonStatus()   { return $this->personStatus;   }
    public function getPersonVerified() { return $this->personVerified; }
    
    public function setName          ($value) { $this->onPropertySet('personName',    $value); }
    public function setPersonId      ($value) { $this->onPropertySet('personId',      $value); }
    public function setPersonName    ($value) { $this->onPropertySet('personName',    $value); }
    public function setPersonStatus  ($value) { $this->onPropertySet('personStatus',  $value); }
    public function setPersonVerified($value) { $this->onPropertySet('personVerified',$value); }
    
    public function eraseCredentials()
    {
        $this->passwordPlain = null;
    }
    public function getRoles()
    {
        if (in_array(self::ROLE_DEFAULT,$this->roles,true)) return $this->roles;
        
        return array_merge(array(self::ROLE_DEFAULT),$this->roles);
    }
    
    // AdvancedUserInterface
    public function isEnabled()               { return  $this->enabled;        }
    public function isAccountNonExpired()     { return !$this->accountExpired;     }
    public function isAccountNonLocked()      { return !$this->accountLocked;      }
    public function isCredentialsNonExpired() { return !$this->credentialsExpired; }
    
    public function setEnabled              ($flag) { $this->onPropertySet('userEnabled',       $flag); }
    public function setAccountNonExpired    ($flag) { $this->onPropertySet('accountExpired',    $flag); }
    public function setAccountNonLocked     ($flag) { $this->onPropertySet('accountLocked',     $flag); }
    public function setCredentialsNonExpired($flag) { $this->onPropertySet('credentialsExpired',$flag); }
   

    
}
?>
