<?php

namespace Cerad\Bundle\PersonBundle\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;


/* ============================================
 * 24 June 2014 - ProjectPerson == PersonPlan
 */
class ProjectPersonRepository extends EntityRepository
{
    public function findAllByProjectPersonKeys($project, $personKeys)
    {
        $qb = $this->createQueryBuilder('projectPerson');
        
        $qb->addSelect('person');
        $qb->leftJoin ('projectPerson.person','person');
        
        $qb->andWhere('projectPerson.projectId = :projectKey');
        $qb->setParameter('projectKey',$project->getKey());
        
        $qb->andWhere('person.guid IN (:personKeys)');
        $qb->setParameter('personKeys',$personKeys);
        
        $fedRole = $project->getFedRole();
        
        $qb->addSelect('personFed');
        $qb->leftJoin ('person.feds','personFed');
            
        $qb->addSelect('personFedCert');
        $qb->leftJoin ('personFed.certs','personFedCert');
            
        $qb->andWhere('personFed.fedRole = :fedRole');
        $qb->setParameter('fedRole',$fedRole);
 
        $qb->orderBy('projectPerson.personName');
        
        return $qb->getQuery()->getResult();
    }
}
?>
