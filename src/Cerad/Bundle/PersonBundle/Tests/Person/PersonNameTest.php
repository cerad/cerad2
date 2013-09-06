<?php

namespace Cerad\Bundle\PersonBundle\Tests\Person;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\PersonBundle\Model\Person;
use Cerad\Bundle\PersonBundle\Model\PersonName;
use Cerad\Bundle\PersonBundle\Model\PersonAddress;

use Cerad\Bundle\PersonBundle\Model\PersonFedOff;

class PersonNameTest extends WebTestCase
{
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
    public function testPersonNameCreate()
    {
        $person = new Person();
        $name1 = $person->createName(array('fullName' => 'Art Hundiak','nickName' => 'Hondo'));
        $this->assertTrue($name1 instanceOf PersonName);
        
        $this->assertEquals ('Art Hundiak',$name1->fullName);
        $this->assertEquals ('Hondo',      $name1->nickName);
        
    }
}
