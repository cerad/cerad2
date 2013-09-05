<?php
namespace Cerad\Bundle\ProjectBundle\Model;

interface ProjectRepositoryInterface
{
    public function find($id);
    
    public function findAll();
    public function findAllByStatus($status);
    
    public function findBySlug($slug);
}
?>
