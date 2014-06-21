<?php
namespace Cerad\Bundle\PersonBundle\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;

class PersonPersonRepository extends EntityRepository
{   
    public function createPersonTeam($params = null) { return $this->createEntity($params); }

    /* ==========================================================
     * Find stuff
     */
    public function findAllByProjectPerson($project,$persons)
    {
        $projectKey = is_object($project) ? $project->getKey() : $project;
        
        if (!is_array($persons)) $persons = array($persons);
        
        $personGuids = array();
        foreach($persons as $person)
        {
            $personGuids[] = is_object($person)  ? $person->getGuid() : $person;
        }
        if (count($personGuids) < 1) return array();
        
        $qb = $this->createQueryBuilder('personTeam');
        
        $qb->select('person,personTeam');
        
        $qb->leftJoin('personTeam.person','person');
        
        $qb->andWhere('personTeam.projectKey = :projectKey');
        $qb->setParameter('projectKey', $projectKey);
        
        $qb->andWhere('person.guid IN (:personGuids)');
        $qb->setParameter('personGuids', $personGuids);
        
      //$qb->orderBy('personTeam.projectKey,personTeam.levelKey,personTeam.teamDesc');
        
        return $qb->getQuery()->getResult();
    }
}
?>
