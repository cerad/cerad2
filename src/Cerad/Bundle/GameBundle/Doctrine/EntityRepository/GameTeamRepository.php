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
        
        $qb->select('game,gameTeam');
        
        $qb->leftJoin('gameTeam.game','game');
        
        $qb->andWhere('game.projectKey = :projectKey');
        $qb->setParameter( 'projectKey', $projectKey);
        
        $qb->andWhere('game.levelKey = :levelKey');
        $qb->setParameter( 'levelKey', $levelKey);
        
        $qb->andWhere('gameTeam.groupSlot = :groupSlot');
        $qb->setParameter(     'groupSlot', $groupSlot);
        
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
            // OK because the import can be bogus
            return array();
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
    public function findAllByTeamKey($teamKey)
    {
        if (!$teamKey) return array();
        
        $qb = $this->createQueryBuilder('gameTeam');
        
        $qb->select('game,gameTeam');
        
        $qb->leftJoin('gameTeam.game','game');
        
        $qb->andWhere('gameTeam.teamKey = :teamKey');
        $qb->setParameter('teamKey',$teamKey);
        
        $qb->addOrderBy('game.levelKey');
        $qb->addOrderBy('game.dtBeg');
        
        return $qb->getQuery()->getResult();
    }
    public function findAllByTeam($team)
    {
        if (!$team) return array();
        return $this->findAllByTeamKey($team->getKey());
    }

}
?>
