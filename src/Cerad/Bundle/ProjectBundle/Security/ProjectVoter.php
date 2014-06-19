<?php

namespace Cerad\Bundle\ProjectBundle\Security;

use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\Role;

/* ==================================================
 * 19 June 2014
 * Check out the voter stuff
 */
class ProjectVoter implements VoterInterface
{
    protected $acl;
    
    // Just because
    protected $accessAbstain = VoterInterface::ACCESS_ABSTAIN;
    protected $accessGranted = VoterInterface::ACCESS_GRANTED;
    protected $accessDenied  = VoterInterface::ACCESS_DENIED;
    
    public function __construct($acl)
    {
        $this->acl = $acl;
    }
    public function supportsAttribute($attribute)
    {
        // Part of the interface, I don't use it
        die('ProjectVoter::supportsAttribute ' . $attribute);
        return in_array($attribute, array(
            self::VIEW,
            self::EDIT,
        ));
    }
    public function supportsClass($class)
    {
        $supportedClass = 'Cerad\Bundle\ProjectBundle\Model\Project';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }
    protected function hasRole($token,$roleName)
    {
        foreach($token->getRoles() as $role)
        {
          //echo $role->getRole() . '<br />';
            if ($role->getRole() == $roleName) return true;
        }
        if ($roleName == 'ROLE_GUEST')
        {
            return count($token->getRoles()) ? false : true;
        }
        return false;
    }
    public function vote(TokenInterface $token, $project, array $attrs)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($project))) {
            return $this->accessAbstain;
        }
        // One attribute for now
        if(count($attrs) != 1) {
            throw new InvalidArgumentException(
                'Only one attribute is allowed for ProjectVoter'
            );
        }
        $attr = $attrs[0];
        $attrParts = explode(':',$attr);
        
        $roleName = $attrParts[0];
        
        // Need to have the role to go any further
        if (!$this->hasRole($token,$roleName)) return $this->accessDenied;
        
        // No property means have access
        if (count($attrParts) < 2) return $this->accessGranted;
        
        // Prop must exist
        $prop = $attrParts[1];

        if (!isset($this->acl[$roleName][$prop])) return $this->accessDenied;
        
        // And check value
        return $this->acl[$roleName][$prop] ? $this->accessGranted : $this->accessDenied;
    }
}
?>
