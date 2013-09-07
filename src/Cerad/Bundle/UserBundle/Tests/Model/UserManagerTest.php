<?php
namespace Cerad\Bundle\UserBundle\Tests\Model;

use Symfony\Component\Security\Core\Encoder\EncoderFactory;
//  Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
//  Cerad\Bundle\UserBundle\Security\UserEncoder as Encoder;

use Cerad\Bundle\UserBundle\Model\User;
use Cerad\Bundle\UserBundle\Model\UserManager;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function getEncoderFactory()
    {
        $config = array(
            'class'     => '\Cerad\Bundle\UserBundle\Security\UserEncoder',
            'arguments' => array('master'),
        );
        $encoders = array('\Cerad\Bundle\UserBundle\Model\User' => $config);
        
        $factory = new EncoderFactory($encoders);
        
        return $factory;
    }
    public function testCreateUser()
    {
        $encoderFactory = $this->getEncoderFactory();
        
        $userManager = new UserManager($encoderFactory);
        
        $user = $userManager->createUser();
        
        $this->assertTrue($user instanceOf User);        
    }
    public function testUpdateUser()
    {
        $encoderFactory = $this->getEncoderFactory();
        
        $userManager = new UserManager($encoderFactory);
        
        $user = $userManager->createUser();
        
        $user->setName    ('Billy The Kid');
        $user->setEmail   ('Bill@Gmail.com');
        $user->setUsername('Bill');
        $user->setPlainPassword('fake');
        
        $userManager->updateUser($user);
        
        $this->assertNull($user->getPlainPassword());
        $this->assertNotEquals('fake',$user->getPassword());

        $this->assertEquals('bill@gmail.com',$user->getEmailCanonical());
        $this->assertEquals('bill',          $user->getUsernameCanonical());
    }
}

?>
