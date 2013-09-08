<?php
namespace Cerad\Bundle\UserBundle\Tests\ValidatorConstraint;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\UserBundle\ValidatorConstraint\EmailUniqueConstraint;
use Cerad\Bundle\UserBundle\ValidatorConstraint\EmailExistsConstraint;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameUniqueConstraint;
use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameExistsConstraint;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameAndEmailUniqueConstraint;
use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameOrEmailExistsConstraint;

/* =====================================================
 * Need to ensure schema exists before running this test
 */
class UserManagerTest extends WebTestCase
{
    protected $managerServiceId = 'cerad_user.user_manager.doctrine';
    protected $repoServiceId    = 'cerad_user.user_repository.doctrine';
    
    protected static $client;
    protected static $container;
 
    public static function setUpBeforeClass()
    {
        self::$client    = static::createClient();
        self::$container = self::$client->getContainer();          
    }    
    protected function getUserRepo()
    {
       $repo = self::$container->get($this->repoServiceId);
       
       return $repo;
    }
    protected function getUserManager()
    {
       $manager = self::$container->get($this->managerServiceId);
       
       return $manager;
    }
    protected function getUserDto()
    {
        $dto = new \stdClass();
        $dto->email         = 'ahundiak02@gmail.com';
        $dto->username      = 'ahundiak02';
        $dto->accountName   = 'Art Hundiak 02';
        $dto->plainPassword = 'zzz';
        return $dto;
    }
    /* ==============================================
     * This is really a data fixture, move to setup
     */
    public function setUp()
    {
        $userManager = $this->getUserManager();
        $dto         = $this->getUserDto();

        $userx = $userManager->findUserByUsername($dto->username);
        if ($userx) return;
        
        $user = $userManager->createUser();
               
        $user->setEmail        ($dto->email);
        $user->setUsername     ($dto->username);
        $user->setAccountName  ($dto->accountName);
        $user->setPlainPassword($dto->plainPassword);
        
        $commit = true;
        $userManager->updateUser($user,$commit);
    }
    public function testUserValidators()
    {   
        $validator = self::$container->get('validator');
        
        $dto = $this->getUserDto();
        $email  = $dto->email;
        $emailx = $dto->email . 'x';
        
        $username  = $dto->username;
        $usernamex = $dto->username . 'x';
        
        // === Email Unique ==============================
        $c1 = new EmailUniqueConstraint();
        
        $c1Pass = $validator->validateValue($emailx,$c1);
        $this->assertEquals(0,count($c1Pass));
        
        $c1Fail = $validator->validateValue($email,$c1);
        $this->assertEquals(1,count($c1Fail));
        
        // === Username Unique ==========================
        $c2 = new UsernameUniqueConstraint();
        
        $c2Pass = $validator->validateValue($dto->username . 'x',$c2);
        $this->assertEquals(0,count($c2Pass));
        
        $c2Fail = $validator->validateValue($dto->username, $c2);
        $this->assertEquals(1,count($c2Fail));
        
       // === Email Exists ================================
        $c3 = new EmailExistsConstraint();
        
        $c3Pass = $validator->validateValue($email,$c3);
        $this->assertEquals(0,count($c3Pass));
        
        $c3Fail = $validator->validateValue($emailx,$c3);
        $this->assertEquals(1,count($c3Fail));
        
       // === Username Exists ================================
        $c4 = new UsernameExistsConstraint();
        
        $c4Pass = $validator->validateValue($username,$c4);
        $this->assertEquals(0,count($c4Pass));
        
        $c4Fail = $validator->validateValue($usernamex,$c4);
        $this->assertEquals(1,count($c4Fail));
        
        // === UsernameAndEmail are Unique ==============================
        $c5 = new UsernameAndEmailUniqueConstraint();
        
        $c5Pass1 = $validator->validateValue($emailx,$c5);
        $this->assertEquals(0,count($c5Pass1));
        
        $c5Pass2 = $validator->validateValue($usernamex,$c5);
        $this->assertEquals(0,count($c5Pass2));
        
        $c5Fail1 = $validator->validateValue($email,$c5);
        $this->assertEquals(1,count($c5Fail1));
        
        $c5Fail2 = $validator->validateValue($username,$c5);
        $this->assertEquals(1,count($c5Fail2));
        
        // === Username Or Email Exist ==============================
        $c6 = new UsernameOrEmailExistsConstraint();
        
        $c6Pass1 = $validator->validateValue($email,$c6);
        $this->assertEquals(0,count($c6Pass1));
        
        $c6Pass2 = $validator->validateValue($username,$c6);
        $this->assertEquals(0,count($c6Pass2));
        
        $c6Fail1 = $validator->validateValue($emailx,$c6);
        $this->assertEquals(1,count($c6Fail1));
        
        $c6Fail2 = $validator->validateValue($usernamex,$c6);
        $this->assertEquals(1,count($c6Fail2));

    }
}
