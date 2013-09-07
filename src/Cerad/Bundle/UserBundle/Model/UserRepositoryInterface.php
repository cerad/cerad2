<?php
namespace Cerad\Bundle\UserBundle\Model;

use Cerad\Bundle\UserBundle\Model\UserInterface as UserModelInterface;

interface UserRepositoryInterface
{
    public function find($id);
    public function findBy ();
    public function findAll();
    
    public function clear();
    
    public function save(UserModelInterface $item);
    public function commit();
    
    static function createUser($params = null);
    
}

?>
