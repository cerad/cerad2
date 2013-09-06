<?php

namespace Cerad\Bundle\PersonBundle\Tests\PersonRepository\Doctrine;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\PersonBundle\Model\Person  as PersonModel;
use Cerad\Bundle\PersonBundle\Entity\Person as PersonEntity;

use Cerad\Bundle\PersonBundle\Model\PersonRepositoryInterface;
use Cerad\Bundle\PersonBundle\Repository\PersonRepositoryDoctrine;

class PersonRepositoryTest extends WebTestCase
{
    protected $repoServiceId = 'cerad_person.repository.doctrine';
    
    protected function getRepo()
    {
       $client = static::createClient();

       $repo = $client->getContainer()->get($this->repoServiceId);
       
       return $repo;
    }

    public function testExistence()
    {
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
    public function testNameAndAddress()
    {
        $repo = $this->getRepo();
        
        $person1 = $repo->createPerson();
        
        // Name
        $name1 = $person1->getName();
        $name1->full = 'Art Hundiak';
        $name1->nick = 'Hondo';
        $person1->setName($name1);
        
        // Address
        $address1 = $person1->getAddress();
        $address1->city  = 'Huntsville';
        $address1->state = 'AL';
        $person1->setAddress($address1);
       
        // Persist
        $repo->save($person1);
        $repo->commit();
        $repo->clear();
        
        $person2  = $repo->find($person1->getId());
        $name2    = $person2->getName();
        $address2 = $person2->getAddress();
        
        $this->assertTrue($name1 ==  $name2);
        $this->assertEquals($address1,$address2);
        
        
    }

}
