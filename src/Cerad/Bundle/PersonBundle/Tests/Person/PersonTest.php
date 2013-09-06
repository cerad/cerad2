<?php

namespace Cerad\Bundle\PersonBundle\Tests\Person;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\PersonBundle\Model\Person;
//  Cerad\Bundle\PersonBundle\Model\PersonName;
use Cerad\Bundle\PersonBundle\Model\PersonAddress;

use Cerad\Bundle\PersonBundle\Model\PersonFed;

class PersonTest extends WebTestCase
{
    public function testPerson()
    {
        $person = new Person();
        $this->assertTrue($person instanceOf Person);
                
        $id = $person->getId();
        $this->assertInternalType('string',$id);
        
        $this->assertEquals(36,strlen($id));
        
        $this->assertInternalType('array', $person->getFeds()); 
        
    }
    public function testPersonAddress()
    {
        $person = new Person();
        
        // Getting back a value object created with the constructor
        $address1 = $person->getAddress();
        $this->assertTrue($address1 instanceOf PersonAddress);
        
        $address2 = new PersonAddress(null,null,'Huntsville','AL');
        $person->setAddress($address2);
        
        // New PersonName object was created because values changed
        $address3 = $person->getAddress();
        $this->assertFalse($address2 === $address3);
        
        $this->assertEquals('Huntsville',$address3->city);
        $this->assertEquals('AL',        $address3->state);
        
    }
    /* ============================================================
     * Really just a test to verify a method can be both static and dynamic
     * Static properties cannot be accessed through an instance
     * But it's okay for methods
     */
    public function testGenders()
    {
        $genders1 = Person::getGenderTypes();
        $this->assertEquals('Female',$genders1[Person::GenderFemale]);
        
        $person = new Person();
        $genders2 = $person->getGenderTypes();
        $this->assertEquals('Male',$genders2[Person::GenderMale]);
    }
    public function testPersonFed()
    {
        $person = new Person();
        $fed = $person->createFed();
        
        $this->assertTrue($fed instanceOf PersonFed);
        
    }
/* =====================================
 * person
 * personFedOff
 * personFedOffOrg
 * personFedOffCert
 * 
 */
}
