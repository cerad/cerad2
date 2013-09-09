<?php
namespace Cerad\Bundle\UserBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Cerad\Bundle\UserBundle\Model\UserManagerInterface;

class UserProvider implements UserProviderInterface
{
    protected $userInterface = 'Cerad\Bundle\UserBundle\Model\UserInterface';
    
    protected $logger;
    protected $dispatcher;
    protected $userManager;
   
    public function __construct(UserManagerInterface $userManager, $dispatcher = null, $logger = null)
    {
        $this->userManager = $userManager;
        $this->dispatcher  = $dispatcher;
        $this->logger = $logger;
        
    }
    public function loadUserByUsername($username)
    {
        // The basic way
        $user1 = $this->userManager->loadByUsernameOrEmail($username);
        if ($user1) return;
        
        // Check for social network identifiers
        
        // See if a fed person exists
        
        // Bail
        throw new UsernameNotFoundException('User Not Fouund ' . $username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!($user instanceOf $this->userInterface))
        {
            throw new UnsupportedUserException();
        }
        return $this->userManager->findUser($user->getId());
    }
    public function supportsClass($class)
    {
        return ($class instanceOf $this->userInterface) ? true: false;
    }
    
}
?>
