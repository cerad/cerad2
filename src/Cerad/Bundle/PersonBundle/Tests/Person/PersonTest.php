<?php

namespace Cerad\Bundle\PersonBundle\Tests\Person;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\PersonBundle\Model\Person;
use Cerad\Bundle\PersonBundle\Model\PersonFedOff;

class PersonTest extends WebTestCase
{
    public function testPerson()
    {
        $person = new Person();
        $this->assertTrue($person instanceOf Person);
        
        $person->setName('Art');
        $this->assertEquals('Art',$person->getName());
        
        $id = $person->getId();
        $this->assertInternalType('string',$id);
        
        $this->assertEquals('CPER',substr($id,0,4));
        
        $this->assertTrue($person->isIdNew());
        
        $this->assertInternalType('array', $person->getFedOffs()); 
        
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
