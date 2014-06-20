<?php

namespace Cerad\Bundle\GameBundle\Action\GameOfficial;

use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class GameOfficialVoter implements VoterInterface
{
    // Just because
    protected $accessAbstain = VoterInterface::ACCESS_ABSTAIN;
    protected $accessGranted = VoterInterface::ACCESS_GRANTED;
    protected $accessDenied  = VoterInterface::ACCESS_DENIED;
    
    public function __construct()
    {  
    }
    public function supportsAttribute($attribute) 
    { 
        switch($attribute)
        {
            case 'AssignableByUser': return true;
        }
        return false;
    }
    public function supportsClass($class) { if ($class); return false;}
    
    public function vote(TokenInterface $token, $info, array $attrs)
    {
         if (!is_array($info)) return $this->accessAbstain;
         
         $attr = $attrs[0];
         if (!$this->supportsAttribute($attr)) return $this->accessAbstain;
         
         switch($attr)
         {
             case 'AssignableByUser': return $this->isAssignableBuUser($info);
         }
         $official = $info['official'];
        
         if ($official->getAssignRole() != 'ROLE_USER') return $this->accessDenied;
    }
    protected function isAssignableBuUser($info)
    {
         $official = $info['official'];
        
         if ($official->getAssignRole() != 'ROLE_USER') return $this->accessDenied;
        
         $officialPersonKey = $official->getPersonKey();
         
         if (!$officialPersonKey)
         {
             // The assignor must have assigned by name
             if (!$official->getPersonNameFull()) return $this->accessGranted;
             
             // Really should not happen
             return $this->accessDenied;
         }
         
         // Assigned to someone.  Is it me?
         $personKeys = $info['personKeys'];
         
         return isset($personKeys[$officialPersonKey]) ? $this->accessGranted : $this->accessDenied;
    }
}
?>
