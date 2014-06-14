<?php
namespace Cerad\Bundle\GameBundle\Doctrine\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;

class GameTeamRepository extends EntityRepository
{   
  //public function createGameTeam($params = null) { return $this->createEntity($params); }

    /* ==========================================================
     * Find stuff
     */
    public function findOneByProjectLevelGroupSlot($projectKey,$levelKey,$groupSlot)
    {
        $qb = $this->createQueryBuilder('gameTeam');
        
        $qb->select('game,gameTeam,team');
        
        $qb->leftJoin('gameTeam.game','game');
        $qb->leftJoin('gameTeam.team','team');
        
        $qb->andWhere('game.projectKey = :projectKey');
        $qb->setParameter('projectKey',$projectKey);
        
        $qb->andWhere('game.levelKey = :levelKey');
        $qb->setParameter('levelKey',$levelKey);
        
        $qb->andWhere('gameTeam.groupSlot = :groupSlot');
        $qb->setParameter('groupSlot',$groupSlot);
        
        $items = $qb->getQuery()->getResult();
        
        if (count($items) != 1) return null;
        
        return $items[0];
    }
}
?>
