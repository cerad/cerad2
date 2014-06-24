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
    
    protected $roleHeirarchy;
    
    public function __construct($roleHeirarchy)
    {  
        $this->roleHeirarchy = $roleHeirarchy;
    }
    public function supportsAttribute($attribute) 
    { 
        switch($attribute)
        {
            case 'AssignableByUser':     return true;
            case 'AssignableByAssignor': return true;
        }
        return false;
    }
    public function supportsClass($class) { if ($class); return false;}
    
    public function vote(TokenInterface $token, $info, array $attrs)
    {         
         $attr = $attrs[0];
         if (!$this->supportsAttribute($attr)) return $this->accessAbstain;
         
         switch($attr)
         {
             case 'AssignableByUser':     return $this->isAssignableByUser    ($info);
             case 'AssignableByAssignor': return $this->isAssignableByAssignor($info,$token);
         }
         return $this->accessDenied;
    }
    protected function isAssignableByUser($info)
    {
         $official = $info['official'];
        
         // Either no role or role user
         $officialAssignRole = $official->getAssignRole();
         if ($officialAssignRole && $officialAssignRole != 'ROLE_USER') return $this->accessDenied;
        
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
    protected function hasRole($token,$target)
    {
        $reachableRoles = $this->roleHeirarchy->getReachableRoles($token->getRoles());
        foreach($reachableRoles as $role)
        {
            if ($role->getRole() == $target) return true;
        }
        return false;
    }
    protected function isAssignableByAssignor($gameOfficial,$token)
    {
        // Must be at least an assignor
        if (!$this->hasRole($token,'ROLE_ASSIGNOR')) return $this->accesDenied;
        
        // ROLE_USER
        $assignRole = $gameOfficial->getAssignRole();
        switch($assignRole)
        {
            case 'ROLE_USER':
            case 'ROLE_ASSIGNOR':
                $levelKey = $gameOfficial->getGame()->getLevelKey();
                if (strpos($levelKey,'Core') !== false)
                {
                    return !$this->hasRole($token,'ROLE_ASSIGNOR_CORE') ? $this->accessGranted : $this->accessDenied;
                }
                if (strpos($levelKey,'Extra') !== false)
                {
                    return !$this->hasRole($token,'ROLE_ASSIGNOR_EXTRA') ? $this->accessGranted : $this->accessDenied;
                }
                return $this->accessDenied;
                
            case 'ROLE_ASSIGNOR_KAC':
                return !$this->hasRole($token,'ROLE_ASSIGNOR_KAC') ? $this->accessGranted : $this->accessDenied;
        }
        return $this->accessDenied;
    }
}
?>
