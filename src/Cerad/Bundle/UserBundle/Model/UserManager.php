<?php
namespace Cerad\Bundle\UserBundle\Model;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use Cerad\Bundle\UserBundle\Model\UserInterface;
use Cerad\Bundle\UserBundle\Model\UserManagerInterface;

class UserManager implements UserManagerInterface
{
    protected $encoderFactory;
    
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }
    public function createUser()
    {
        return new User();
    }
    public function updateUser(UserInterface $user, $commit = true)
    {
        $this->updatePassword       ($user);
        $this->updateCanonicalFields($user);
    }
    protected function updateCanonicalFields(UserInterface $user)
    {
        $user->setEmailCanonical   ($this->canonicalizeEmail   ($user->getEmail()));
        $user->setUsernameCanonical($this->canonicalizeUsername($user->getUsername()));
    }
    protected function canonicalizeEmail($email)
    {
        return strtolower($email);
    }
    protected function canonicalizeUsername($username)
    {
        return strtolower($username);
    }
    protected function updatePassword(UserInterface $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $encoder = $this->getEncoder($user);
            $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
            $user->eraseCredentials();
        }
    }
    protected function getEncoder(UserInterface $user)
    {
        return $this->encoderFactory->getEncoder($user);
    }

}
?>
