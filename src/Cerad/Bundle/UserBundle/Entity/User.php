<?php
namespace Cerad\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Cerad\Bundle\UserBundle\Model\User as UserModel;

class User extends UserModel
{   
    protected $id;
    
    public function getId() { return $this->id;                }

    public function __construct()
    {
        parent::__construct();

        $this->authens = new ArrayCollection();
    }
}
?>
