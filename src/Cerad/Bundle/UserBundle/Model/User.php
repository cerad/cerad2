<?php
namespace Cerad\Bundle\UserBundle\Model;

use Cerad\Bundle\UserBundle\Model\UserInterface;

class User extends BaseModel implements UserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';
    
    protected $id;
    protected $salt;
    
    protected $email;
    protected $emailCanonical;
    protected $emailConfirmed = false;
    
    protected $username;
    protected $usernameCanonical;
    
    protected $password;
    protected $passwordHint;
    protected $passwordPlain;
        
    protected $roles = array();
    
    // Wants to be a value object
    protected $personId;
    protected $personStatus    = 'Active';
    protected $personVerified  = 'No';
    protected $personConfirmed = false;
    
    protected $identifiers; // User Identifiers
    
    protected $accountName;
    protected $accountEnabled     = true;  // After first created
    protected $accountLocked      = false;
    protected $accountExpired     = false;
    protected $accountExpiresAt;
    protected $credentialsExpired = false;
    protected $credentialsExpireAt;
    
    // More value objects
    protected $passwordResetToken;
    protected $passwordResetRequestedAt;
    protected $passwordResetRequestExpiresAt;
    
    protected $emailConfirmToken;
    protected $emailConfirmRequestedAt;
    protected $emailConfirmRequestExpiresAt;
    
    protected $personConfirmToken;
    protected $personConfirmRequestedAt;
    protected $personConfirmRequestExpiresAt;
       
    // These are just events
    protected $accountCreatedOn;
    protected $accountUpdatedOn;
    protected $accountLastLoginOn;
    
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
    public function getPasswordHint()      { return $this->passwordHint;      }
    public function getPasswordPlain()     { return $this->passwordPlain;     }
    public function getPlainPassword()     { return $this->passwordPlain;     }
    public function getConfirmationToken() { return $this->confirmationToken; }
    
    public function setId               ($value) { $this->onPropertySet('id',               $value); }
    public function setSalt             ($value) { $this->onPropertySet('salt',             $value); }
    public function setEmail            ($value) { $this->onPropertySet('email',            $value); }
    public function setEmailCanonical   ($value) { $this->onPropertySet('emailCanonical',   $value); }
    public function setUsername         ($value) { $this->onPropertySet('username',         $value); }
    public function setUsernameCanonical($value) { $this->onPropertySet('usernameCanonical',$value); }
    public function setPassword         ($value) { $this->onPropertySet('password',         $value); }
    public function setPasswordHint     ($value) { $this->onPropertySet('passwordHint',     $value); }
    public function setPasswordPlain    ($value) { $this->onPropertySet('passwordPlain',    $value); }
    public function setPlainPassword    ($value) { $this->onPropertySet('passwordPlain',    $value); }
    public function setConfirmationToken($value) { $this->onPropertySet('confirmationToken',$value); }
    
    /* =======================================================
     * My person link
     */
    public function getPersonId()       { return $this->personId;       }
    public function getPersonStatus()   { return $this->personStatus;   }
    public function getPersonVerified() { return $this->personVerified; }
    
    public function setName          ($value) { $this->onPropertySet('personName',    $value); }
    public function setPersonId      ($value) { $this->onPropertySet('personId',      $value); }
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
    
    // Account - AdvancedUserInterface
    public function getAccountName()          { return $this->accountName;     }
   
    public function isEnabled()               { return  $this->accountEnabled;     }
    public function isAccountEnabled()        { return !$this->accountEnabled;     }
    public function isAccountNonExpired()     { return !$this->accountExpired;     }
    public function isAccountNonLocked()      { return !$this->accountLocked;      }
    public function isCredentialsNonExpired() { return !$this->credentialsExpired; }
    
    public function setAccountName          ($name) { $this->onPropertySet('accountName',       $name); }
    public function setAccountEnabled       ($flag) { $this->onPropertySet('accountEnabled',    $flag); }
    public function setAccountNonExpired    ($flag) { $this->onPropertySet('accountExpired',    $flag); }
    public function setAccountNonLocked     ($flag) { $this->onPropertySet('accountLocked',     $flag); }
    public function setCredentialsNonExpired($flag) { $this->onPropertySet('credentialsExpired',$flag); }

    /* =========================================================================
     * Identifiers stuff
     */
    public function getIdentifiers() { return $this->identifiers; }
}
?>
