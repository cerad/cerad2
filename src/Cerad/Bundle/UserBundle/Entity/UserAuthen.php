<?php
namespace Cerad\Bundle\UserBundle\Entity;

use Cerad\Bundle\UserBundle\Model\UserAuthen as UserAuthenModel;

class UserAuthen extends UserAuthenModel
{   
    protected $id;
    
    public function getId() { return $this->id; }
    
    public function setId($oauth) { $this->onPropertySet('id', $oauth); }

    public function __construct()
    {
        parent::__construct();
    }
}
?>
