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
        die('findOneByProjectLevelGroupSlot');
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
    public function findAllByProjectLevelGroupSlot($projectKey,$levelKey,$group)
    {
        if (!$group) return array();
        $groupParts = explode(':',$group);
        if (count($groupParts) != 3)
        {
            throw new \Exception('Invalid group arg: ' . $group);
        }
        $groupType = $groupParts[0];
        $groupName = $groupParts[1];
        $groupSlot = $groupParts[2];
        
        $qb = $this->createQueryBuilder('gameTeam');
        
        $qb->select('game,gameTeam');
        
        $qb->leftJoin('gameTeam.game','game');
        
        $qb->andWhere('game.projectKey = :projectKey');
        $qb->setParameter('projectKey',$projectKey);
        
        $qb->andWhere('game.levelKey = :levelKey');
        $qb->setParameter('levelKey',$levelKey);
        
        $qb->andWhere('game.groupType = :groupType');
        $qb->setParameter('groupType',$groupType);
        
        $qb->andWhere('game.groupName = :groupName');
        $qb->setParameter('groupName',$groupName);
        
        $qb->andWhere('gameTeam.groupSlot = :groupSlot');
        $qb->setParameter('groupSlot',$groupSlot);
        
        return $qb->getQuery()->getResult();
    }
    public function findAllByProjectLevel($projectKey,$levelKey = null)
    {
        $qb = $this->createQueryBuilder('gameTeam');
        
        $qb->select('game,gameTeam');
        
        $qb->leftJoin('gameTeam.game','game');
        
        $qb->andWhere('game.projectKey = :projectKey');
        $qb->setParameter('projectKey',$projectKey);
        
        if ($levelKey)
        {
            $qb->andWhere('game.levelKey = :levelKey');
            $qb->setParameter('levelKey',$levelKey);
        }
        $qb->addOrderBy('game.levelKey');
        $qb->addOrderBy('game.dtBeg');
        
        return $qb->getQuery()->getResult();
    }
    public function findAllByProjectLevelTeamNum($projectKey,$levelKey,$teamNum)
    {
        if (!$teamNum) return array();
        
        $qb = $this->createQueryBuilder('gameTeam');
        
        $qb->select('game,gameTeam');
        
        $qb->leftJoin('gameTeam.game','game');
        
        $qb->andWhere('game.projectKey = :projectKey');
        $qb->setParameter('projectKey',$projectKey);
        
        $qb->andWhere('game.levelKey = :levelKey');
        $qb->setParameter('levelKey',$levelKey);
        
        // TODO: Maybe handle multiple team numbers?
        $qb->andWhere('gameTeam.teamNum = :teamNum');
        $qb->setParameter('teamNum',$teamNum);
        
        $qb->addOrderBy('game.levelKey');
        $qb->addOrderBy('game.dtBeg');
        
        return $qb->getQuery()->getResult();
    }
}
?>
