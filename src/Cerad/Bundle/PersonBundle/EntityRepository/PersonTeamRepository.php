<?php
namespace Cerad\Bundle\PersonBundle\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;

class PersonTeamRepository extends EntityRepository
{   
    public function createPersonTeam($params = null) { return $this->createEntity($params); }

    /* ==========================================================
     * Find stuff
     */
    public function findAllByProjectPerson($project,$person)
    {
        $projectKey = is_object($project) ? $project->getKey() : $project;
        $personGuid = is_object($person)  ? $person->getGuid() : $person;
        
        $qb = $this->createQueryBuilder('personTeam');
        
        $qb->select('person,personTeam');
        
        $qb->leftJoin('personTeam.person','person');
        
        $qb->andWhere('personTeam.projectKey = :projectKey');
        $qb->setParameter('projectKey', $projectKey);
        
        $qb->andWhere('person.guid = :personGuid');
        $qb->setParameter('personGuid', $personGuid);
        
      //$qb->orderBy('personTeam.projectKey,personTeam.levelKey,personTeam.teamDesc');
        
        return $qb->getQuery()->getResult();
    }
}
?>
