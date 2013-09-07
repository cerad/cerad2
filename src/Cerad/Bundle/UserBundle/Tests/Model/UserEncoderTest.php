<?php
namespace Cerad\Bundle\UserBundle\Tests\Model;

use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

use Cerad\Bundle\UserBundle\Model\User           as UserModel;
use Cerad\Bundle\UserBundle\Security\UserEncoder as Encoder;

class UserEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testEncoder()
    {
        $passwordMaster = 'master';
        $encoder = new Encoder($passwordMaster);
        
        $this->assertTrue($encoder instanceOf PasswordEncoderInterface);
        $this->assertTrue($encoder instanceOf Encoder);
        
        $salt  = 'salt';
        $passwordPlain = 'aaa';
        
        $passwordEncoded = $encoder->encodePassword($passwordPlain, $salt);
        $this->assertNotNull($passwordEncoded);
        
        $this->assertTrue($encoder->isPasswordValid($passwordEncoded, $passwordPlain,  $salt));
        $this->assertTrue($encoder->isPasswordValid($passwordEncoded, $passwordMaster, $salt));
    }
    public function testEncoderFactory()
    {
        $config = array(
            'class'     => '\Cerad\Bundle\UserBundle\Security\UserEncoder',
            'arguments' => array('master'),
        );
        $encoders = array('\Cerad\Bundle\UserBundle\Model\User' => $config);
        
        $factory = new EncoderFactory($encoders);
        
        $user = new UserModel();
        
        $encoder = $factory->getEncoder($user);
        $this->assertTrue($encoder instanceOf Encoder);
       
    }
}

?>
