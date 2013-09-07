<?php

namespace Cerad\Bundle\UserBundle\Tests\Doctrine\UserManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

use Cerad\Bundle\UserBundle\Model \User        as UserModel;
use Cerad\Bundle\UserBundle\Entity\User        as UserEntity;
use Cerad\Bundle\UserBundle\Entity\UserManager as UserManager;

class PersonRepositoryTest extends WebTestCase
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
    protected function getRepo()
    {
       $repo = self::$container->get($this->repoServiceId);
       
       return $repo;
    }
    protected function getManager()
    {
       $manager = self::$container->get($this->managerServiceId);
       
       return $manager;
    }
    public function testExistence()
    {
        return;
        $repo = $this->getRepo();
        $this->assertTrue($repo instanceOf PersonRepositoryInterface);
        $this->assertTrue($repo instanceOf PersonRepositoryDoctrine);
        
        $person1 = $repo->createPerson();
        $this->assertTrue($person1 instanceOf PersonModel);
        $this->assertTrue($person1 instanceOf PersonEntity);
        $person1 = null;
        
        $personClassName = $repo->getClassName();
        $person2 = new $personClassName();
        $this->assertTrue($person2 instanceOf PersonModel);
        $this->assertTrue($person2 instanceOf PersonEntity);
        $person2 = null;
    }
    public function testCommit()
    {
        return;
        
        $repo = $this->getRepo();
        
        $person1 = $repo->createPerson();
        $person1->setEmail('ahundiak01@gmail.com');
        
        $repo->save($person1);
        $repo->commit();
        $repo->clear();
        
        $person2 = $repo->find($person1->getId());
        $this->assertFalse($person1 === $person2);
        
        $this->assertEquals($person1->getId(),   $person2->getId());
        $this->assertEquals($person1->getEmail(),$person2->getEmail());
        
        // This is not true probably because of the array collections?
        //$this->assertTrue ($person1 ==  $person2);
    }
}
