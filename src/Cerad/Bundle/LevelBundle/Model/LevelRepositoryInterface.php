<?php
namespace Cerad\Bundle\LevelBundle\Model;

interface LevelRepositoryInterface
{
    public function find($id);
    
    public function findAll();

}
?>
