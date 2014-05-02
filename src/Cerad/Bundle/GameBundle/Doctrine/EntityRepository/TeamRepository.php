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
}
?>
