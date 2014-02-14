<?php

namespace Cerad\Bundle\GameBundle\EntityRepository;

use Doctrine\ORM\EntityRepository;

use Cerad\Bundle\GameBundle\Entity\GameField as GameFieldEntity;

class GameFieldRepository extends EntityRepository
{   
    public function createGameField($config = null) { return new GameFieldEntity($config); }

    public function find($id)
    {
        return $id ? parent::find($id) : null;
    }
    public function findOneByProjectName($projectId, $name)
    {
        return $this->findOneBy(array('projectId' => $projectId, 'name' => $name));    
    }
    public function findByProject($project)
    {
        $projectKey = is_object($project) ? $project->getKey() : $project;
        
        return $this->findBy(array('projectId' => $projectKey), array('venue' => 'ASC', 'name' => 'ASC'));    
    }

    /* ==========================================================
     * Persistence
     */
    public function save($entity)
    {
        if ($entity instanceof GameFieldEntity) 
        {
            $em = $this->getEntityManager();

            return $em->persist($entity);
        }
        throw new \Exception('Wrong type of entity for save');
    }
    public function commit()
    {
       $em = $this->getEntityManager();
       return $em->flush();
    }
}
?>
