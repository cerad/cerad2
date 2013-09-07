<?php
namespace Cerad\Bundle\UserBundle\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface UserInterface extends AdvancedUserInterface
{
    // Basic account
    public function getId();
    public function setId($value); // For imports and merging
    
    public function getEmail();
    public function setEmail($value);

    public function getUsername();
    public function setUsername($value);
    
    public function getPassword();
    public function setPassword($value);
    
    public function getPlainPassword();
    public function setPlainPassword($value);
    
    public function getConfirmationToken();
    public function setConfirmationToken($value);
    
    public function isEnabled();
    public function setAccountEnabled($bool);
    
    // Basic person
    public function getName();
    public function setName($value);
    
    public function getPersonId();
    public function setPersonId($value);
    
    public function getPersonName();
    public function setPersonName($value);

    public function getPersonStatus();
    public function setPersonStatus($value);
    
    public function getPersonVerified();
    public function setPersonVerified($value);
    
}

?>
