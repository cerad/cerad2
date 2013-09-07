<?php

namespace Cerad\Bundle\UserBundle\Tests\Entity\UserManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\Collections\ArrayCollection;

use Cerad\Bundle\UserBundle\Model\User                    as UserModel;
use Cerad\Bundle\UserBundle\Model\UserInterface           as UserModelInterface;
use Cerad\Bundle\UserBundle\Model\UserManager             as UserModelManager;
//  Cerad\Bundle\UserBundle\Model\UserRepositoryInterface as UserModelRepositoryInerface;

use Cerad\Bundle\UserBundle\Entity\User           as UserEntity;
use Cerad\Bundle\UserBundle\Entity\UserManager    as UserEntityManager;
//  Cerad\Bundle\UserBundle\Entity\UserRepository as UserEntityRepository;

use Cerad\Bundle\UserBundle\Validator\Constraints\EmailUnique    as EmailUniqueConstraint;
use Cerad\Bundle\UserBundle\Validator\Constraints\EmailExists    as EmailExistsConstraint;

use Cerad\Bundle\UserBundle\Validator\Constraints\UsernameUnique as UsernameUniqueConstraint;
use Cerad\Bundle\UserBundle\Validator\Constraints\UsernameExists as UsernameExistsConstraint;

use Cerad\Bundle\UserBundle\Validator\Constraints\UsernameAndEmailUnique as UsernameAndEmailUniqueConstraint;
use Cerad\Bundle\UserBundle\Validator\Constraints\UsernameOrEmailExists  as UsernameOrEmailExistsConstraint;

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
        
        /* ======================================
         * Drop and build the schema
         * TODO: figure out how to have test point to a different database
         */
        $em = self::$container->get('cerad_user.entity_manager.doctrine');
        $metaDatas = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        
        $schemaTool->dropSchema  ($metaDatas);
        $schemaTool->createSchema($metaDatas);
        
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
        $dto->email         = 'ahundiak01@gmail.com';
        $dto->username      = 'ahundiak01';
        $dto->accountName   = 'Art Hundiak';
        $dto->plainPassword = 'zzz';
        return $dto;
    }
    public function testCreateUser()
    {
        $userManager = $this->getUserManager();
        $this->assertTrue($userManager instanceOf UserEntityManager);
        $this->assertTrue($userManager instanceOf UserModelManager);
       
        $user = $userManager->createUser();
        $this->assertTrue($user instanceOf UserEntity);
        $this->assertTrue($user instanceOf UserModel);
        $this->assertTrue($user instanceOf UserModelInterface);
        
        $identifiers = $user->getIdentifiers();
        $this->assertTrue($identifiers instanceOf ArrayCollection);
        
        $dto = $this->getUserDto();
        $user->setEmail        ($dto->email);
        $user->setUsername     ($dto->username);
        $user->setAccountName  ($dto->accountName);
        $user->setPlainPassword($dto->plainPassword);
        
        $commit = true;
        $userManager->updateUser($user,$commit);
        
        return $user->getId();
    }
    /**
     * @depends testCreateUser
     */
    public function testFindUser($userId)
    {
        $dto = $this->getUserDto();
         
        $userManager = $this->getUserManager();
        
        $user1 = $userManager->findUser($userId);
        $this->assertTrue($user1 instanceOf UserEntity);
        $this->assertEquals($dto->accountName,$user1->getAccountName());
        
        $user2 = $userManager->findUserByEmail($dto->email);
        $this->assertTrue($user2 instanceOf UserEntity);
        $this->assertEquals($dto->email,$user2->getEmail());
        
        $user3 = $userManager->findUserByUsername($dto->username);
        $this->assertTrue($user3 instanceOf UserEntity);
        $this->assertEquals($dto->username,$user3->getUsername());
        
        $user4 = $userManager->findUserByUsernameOrEmail($dto->username);
        $this->assertTrue($user4 instanceOf UserEntity);
        $this->assertEquals($dto->username,$user4->getUsername());
        
        $user5 = $userManager->findUserByUsernameOrEmail($dto->email);
        $this->assertTrue($user5 instanceOf UserEntity);
        $this->assertEquals($dto->email,$user5->getEmail());
        
        $user6 = $userManager->findUserByUsernameOrEmail('does not exist');
        $this->assertNull($user6);
        
        $users = $userManager->findUsers();
        $this->assertEquals(1,count($users));
    }
    /**
     * @depends testCreateUser
     */
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
