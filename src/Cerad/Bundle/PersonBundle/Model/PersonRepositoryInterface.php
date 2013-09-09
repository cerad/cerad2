<?php

namespace Cerad\Bundle\PersonBundle\Model;

use Cerad\Bundle\PersonBundle\Model\Person as PersonModel;

interface PersonRepositoryInterface
{
    public function find($id);
    public function findAll();
    
    public function findFed($id);
    
    public function clear();
    
    public function save(PersonModel $item);
    public function commit();
}
?>
