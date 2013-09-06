<?php

namespace Cerad\Bundle\PersonBundle\Tests\Person;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\PersonBundle\Model\Person;
use Cerad\Bundle\PersonBundle\Model\PersonName;
use Cerad\Bundle\PersonBundle\Model\PersonAddress;

use Cerad\Bundle\PersonBundle\Model\PersonFedOff;

class PersonTest extends WebTestCase
{
    public function testPerson()
    {
        $person = new Person();
        $this->assertTrue($person instanceOf Person);
                
        $id = $person->getId();
        $this->assertInternalType('string',$id);
        
        $this->assertEquals(36,strlen($id));
        
        $this->assertInternalType('array', $person->getFedOffs()); 
        
    }
    /* ==============================================
     * So PersonName is a value object
     * Getting and setting will always create anew object even if the values are identical
     * This prevents unwanted side effects caused when you change the
     * properties of a retrieved object
     */
    public function testPersonName()
    {
        $person = new Person();
        
        // Getting back a value object created with the constructor
        $personName1 = $person->getName();
        $this->assertTrue($personName1 instanceOf PersonName);
       
        $personName2 = new PersonName('Art Hundiak','Arthur','Hundiak','Hondo');
        $person->setName($personName2);
        
        // New PersonName object was created because values changed
        $personName3 = $person->getName();
        $this->assertFalse($personName1 === $personName3);
        
        // This shows that even though the values are different, have different object
        $this->assertEquals ($personName2,$personName3);
        $this->assertNotSame($personName2,$personName3);
        
        $this->assertTrue ($personName2 ==  $personName3);
        $this->assertFalse($personName2 === $personName3);
        
        // Verify some values
        $this->assertEquals ('Arthur',$personName3->firstName);
        $this->assertEquals ('Hondo', $personName3->nickName);
        
        /* =================================================
         * Normally you would not do this but forms will
         * So verify that the person's name object is not impacted
         */
        $personName3->firstName = 'Art';
        $personName4 = $person->getName();

        $this->assertNotEquals($personName3,$personName4);
        $this->assertNotSame  ($personName3,$personName4);
        
        $this->assertFalse($personName3 ==  $personName4);
        $this->assertFalse($personName3 === $personName4);       
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
    public function testPersonFedOff()
    {
        $person = new Person();
        $personFedOff = $person->createFedOff();
        
        $this->assertTrue($personFedOff instanceOf PersonFedOff);
        
    }
/* =====================================
 * person
 * personFedOff
 * personFedOffOrg
 * personFedOffCert
 * 
 */
}
