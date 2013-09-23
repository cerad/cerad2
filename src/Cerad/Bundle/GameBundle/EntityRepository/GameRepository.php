<?php

namespace Cerad\Bundle\GameBundle\EntityRepository;

use Doctrine\ORM\EntityRepository;

use Cerad\Bundle\GameBundle\Entity\Game as GameEntity;

class GameRepository extends EntityRepository
{   
    public function createGame($params = null) { return new GameEntity($params); }

    /* ==========================================================
     * Find stuff
     */
    public function find($id)
    {
        return $id ? parent::find($id) : null;
    }
    public function findOneByProjectNum($projectId,$num)
    {
        return $this->findOneBy(array('projectId' => $projectId, 'num' => $num));    
    }
    /* ==========================================================
     * Persistence
     */
    public function save($entity)
    {
        if ($entity instanceof GameEntity) 
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
    /* ========================================================
     * Generic schedule query
     * criteria is just an array
     */
    public function queryGameSchedule($criteria)
    {
        $nums     = $this->getArrayValue($criteria,'nums');
        $dates    = $this->getArrayValue($criteria,'dates');
        $levels   = $this->getArrayValue($criteria,'levels');
        $projects = $this->getArrayValue($criteria,'projects');
        
        $teamNames  = $this->getArrayValue($criteria,'teams');
        $fieldNames = $this->getArrayValue($criteria,'fields');
        
        /* =================================================
         * Dates are always so much fun
         */
        $date1 = $this->getScalerValue($criteria,'date1');
        $date2 = $this->getScalerValue($criteria,'date2');
        
        // Need more work here, both are on then select two dates OR op
        $date1On = $this->getScalerValue($criteria,'date1On');
        $date2On = $this->getScalerValue($criteria,'date2On');
        
        $date1Ignore = $this->getScalerValue($criteria,'date1Ignore');
        $date2Ignore = $this->getScalerValue($criteria,'date2Ignore');
        
        if ($date1On && !$date2On) $date2 = null;
        if ($date2On && !$date1On) $date1 = null;
        
        if ($date1Ignore) $date1 = null;
        if ($date2Ignore) $date2 = null;
        
        if (!$date1) $date1On = false;
        if (!$date2) $date2On = false;
        
        if ($date1 && $date2 && ($date1 > $date2))
        {
            $tmp = $date1; $date1 = $date2; $date2 = $tmp;
        }
        
        /* ===========================================
         * Game ID query
         */
        $qb = $this->createQueryBuilder('game');
     
        $qb->select('distinct game.id');
        $qb->leftJoin ('game.field','gameField');
        $qb->leftJoin ('game.teams','gameTeam');
        
        if ($projects)
        {
            $qb->andWhere('game.projectId IN (:projectIds)');
            $qb->setParameter('projectIds',$projects);
        }
        if ($fieldNames)
        {
            $qb->andWhere('gameField.name IN (:fieldNames)');
            $qb->setParameter('fieldNames',$fieldNames);
        }
        if ($levels)
        {
            $qb->andWhere('gameTeam.levelId IN (:levelIds)');
            $qb->setParameter('levelIds',$levels);
        }
        if ($dates)
        {
            $qb->andWhere('DATE(game.dtBeg) IN (:dates)');
            $qb->setParameter('dates',$dates);
        }
        /* ============================================
         * This is what makes me grab gamesIds first
         */
        if ($teamNames)
        {
            $qb->andWhere('gameTeam.name IN (:teamNames)');
            $qb->setParameter('teamNames',$teamNames);
        }
        
        if ($date1On and $date2On)
        {
           $qb->andWhere('((DATE(game.dtBeg) = :date1) OR (DATE(game.dtBeg) = :date2))');
           $qb->setParameter('date1',$date1);
           $qb->setParameter('date2',$date2);
           $date1 = null;
           $date2 = null;
        }
        if ($date1)
        {
            $op = $date1On ? '=' : '>=';
            $qb->andWhere('DATE(game.dtBeg) ' . $op . ' (:date1)');
            $qb->setParameter('date1',$date1);
        }
        if ($date2)
        {
            $op = $date2On ? '=' : '<=';
            $qb->andWhere('DATE(game.dtEnd) ' . $op . ' (:date2)');
            $qb->setParameter('date2',$date2);
        }
        if ($nums)
        {
            $qb->andWhere('game.num IN (:nums)');
            $qb->setParameter('nums',$criteria['nums']);
        }
      //echo $qb->getDql();
        $gameIds = $qb->getQuery()->getArrayResult();
        if (count($gameIds) < 1) return array();
        
        $ids = array();
        foreach($gameIds as $gameId)
        {
            $ids[] = $gameId['id'];
        }
        
        // Game query
        $qbx = $this->createQueryBuilder('game');
        $qbx->addSelect('gameTeam');
        $qbx->leftJoin ('game.teams','gameTeam');
        $qbx->andWhere ('game.id IN (:ids)');
        $qbx->setParameter('ids',$ids);
        
      // Sadly, this does not work, need to keep experimenting
      //$qbx->andWhere ('game.id IN (' . $qb->getDql() . ')');

        $qbx->addOrderBy('game.dtBeg');

        
        return $qbx->getQuery()->getResult();
    }
    /* ========================================================
     * Distinct list of field names for a set of projects
     */
    public function queryFieldChoices($criteria = array())
    {
        $projectIds = $this->getArrayValue($criteria,'projectIds');
        
        // Build query
        $qb = $this->createQueryBuilder('game');
        
        $qb->select('distinct game.field');
        
        if ($projectIds)
        {
            $qb->andWhere('game.projectId IN (:projectIds)');
            $qb->setParameter('projectIds',$projectIds);
        }
        $qb->addOrderBy('game.field');
       
        $items = $qb->getQuery()->getArrayResult();
        
        $choices = array();
        foreach($items as $item)
        {
            $choices[$item['field']] = $item['field'];
        }
        return $choices;
    }
    /* ========================================================
     * Distinct list of team names for a set of projects and levels
     */
    public function queryTeamChoices($criteria = array())
    {
        $levelIds   = $this->getArrayValue($criteria,'levelIds');
        $projectIds = $this->getArrayValue($criteria,'projectIds');
        
        // Build query
        $qb = $this->createQueryBuilder('game');
        
        $qb->select('distinct gameTeam.name');
        
        $qb->leftJoin('game.teams','gameTeam');
        
        if ($projectIds)
        {
            $qb->andWhere('game.projectId IN (:projectIds)');
            $qb->setParameter('projectIds',$projectIds);
        }
        if ($levelIds)
        {
            $qb->andWhere('gameTeam.levelId IN (:levelIds)');
            $qb->setParameter('levelIds',$levelIds);
        }
        $qb->addOrderBy('gameTeam.name');
       
        $items = $qb->getQuery()->getArrayResult();
        
        $choices = array();
        foreach($items as $item)
        {
            $choices[$item['name']] = $item['name'];
        }
        return $choices;
    }
    /* ========================================================
     * For pulling stuff out of criteria
     */
    protected function getScalerValue($criteria,$name)
    {
        if (!isset($criteria[$name])) return null;

        return $criteria[$name];
    }
    protected function getArrayValue($criteria,$name)
    {
        if (!isset($criteria[$name])) return null;
        
        $value = $criteria[$name];
        
        if (!is_array($value)) return array($value);
        
        if (count($value) < 1) return null;
        
        // This nonsense filters out 0 or null values
        $values  = $value;
        $valuesx = array();
        foreach($values as $value)
        {
            if ($value) $valuesx[] = $value;
        }
        if (count($valuesx) < 1) return null;
        
        return $valuesx;
    }
}
?>
