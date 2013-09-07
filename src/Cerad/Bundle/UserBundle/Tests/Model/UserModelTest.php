<?php
namespace Cerad\Bundle\UserBundle\Tests\Model;

use Cerad\Bundle\UserBundle\Model\User as ModelUser;

class UserModelTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $user1 = new ModelUser();
        
        // Need FQN, maybe in 5,5
      //$this->assertInstanceOf('ModelUser',$user1);
        $this->assertTrue($user1 instanceOf ModelUser);
        
        $salt = $user1->getSalt();
        $this->assertGreaterThan(20,strlen($salt));
        
        $roles = $user1->getRoles();
        $this->assertTrue(is_array($roles));
        $this->assertTrue(in_array('ROLE_USER',$roles,true));
        
        $user1->getPassword();
        $user1->getUsername();
        $user1->eraseCredentials();
        
        /* ===========================================
         * Symfony\Component\Security\Core\User\UserInterface
         * Is now implemented
         */
        $this->assertTrue($user1->isEnabled());
        $this->assertTrue($user1->isAccountNonExpired());
        $this->assertTrue($user1->isAccountNonLocked());
        $this->assertTrue($user1->isCredentialsNonExpired());

        /* =============================================
         * Symfony\Component\Security\Core\User\AdvancedUserInterface
         * is now implemented
         */
        //$this->assertEquals(36,strlen($user1->getId()));
        
    }
}

?>
