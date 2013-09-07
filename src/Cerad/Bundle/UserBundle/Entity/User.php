<?php
namespace Cerad\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Cerad\Bundle\UserBundle\Model\User as UserModel;

class User extends UserModel
{   
    public function __construct()
    {
        parent::__construct();

        $this->identifiers = new ArrayCollection();
    }
}
?>
