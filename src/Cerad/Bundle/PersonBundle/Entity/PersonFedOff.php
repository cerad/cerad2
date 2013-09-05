<?php
namespace Cerad\Bundle\PersonBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Cerad\Bundle\PersonBundle\Model\PersonFedOff as PersonFedOffModel;;

class PersonFedOff extends PersonFedOffModel
{   
    public function __construct()
    {
        $this->orgs  = new ArrayCollection();
        $this->certs = new ArrayCollection();
        
    }
}
?>
