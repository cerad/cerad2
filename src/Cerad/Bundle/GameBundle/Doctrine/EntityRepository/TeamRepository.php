<?php
namespace Cerad\Bundle\GameBundle\Doctrine\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;

class TeamRepository extends EntityRepository
{   
    public function createTeam($params = null) { return $this->createEntity($params); }

    /* ==========================================================
     * Find stuff
     */
    public function findOneByProjectLevelNum($project,$level,$num)
    {
        $num = (int)$num;
        if (!$num) return null;
        
        $levelKey   = is_object($level)   ? $level->getKey()   : $level;
        $projectKey = is_object($project) ? $project->getKey() : $project;
        
        return $this->findOneBy(array('projectKey' => $projectKey, 'levelKey' => $levelKey, 'num' => $num));    
    }
    public function findOneByProjectLevelName($project,$level,$name)
    {
        if (!$name) return null;
        
        $levelKey   = is_object($level)   ? $level->getKey()   : $level;
        $projectKey = is_object($project) ? $project->getKey() : $project;
        
        return $this->findOneBy(array('projectKey' => $projectKey, 'levelKey' => $levelKey, 'name' => $name));    
    }
    public function findAllByProjectLevels($projectKey,$levelKeys)
    {
        $qb = $this->createQueryBuilder('team');
        
        $qb->select('team');
        
        $qb->andWhere('team.projectKey = :projectKey');
        $qb->setParameter('projectKey',$projectKey);
        
        $qb->andWhere('team.levelKey IN (:levelKeys)');
        $qb->setParameter('levelKeys',$levelKeys);
        
        $qb->addOrderBy('team.levelKey');
        $qb->addOrderBy('team.num');
        
        return $qb->getQuery()->getResult();
        
    }
}
?>
