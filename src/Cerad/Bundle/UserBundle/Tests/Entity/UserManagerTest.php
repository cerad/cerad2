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
        
        $authens = $user->getAuthens();
        $this->assertTrue($authens instanceOf ArrayCollection);
        
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
}
