<?php
namespace Cerad\Bundle\GameBundle\Doctrine\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;

class TeamRepository extends EntityRepository
{   
    public function createTeam($params = null) { return $this->createEntity($params); }

    /* ==========================================================
     * Find stuff
     */
    public function findOneByProjectNum($projectKey,$num)
    {
        return $this->findOneBy(array('projectKey' => $projectKey, 'num' => $num));    
    }
}
?>
