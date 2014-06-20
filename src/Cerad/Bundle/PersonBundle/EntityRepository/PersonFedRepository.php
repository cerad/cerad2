<?php
namespace Cerad\Bundle\PersonBundle\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;

class PersonFedRepository extends EntityRepository
{
    /* ==========================================================
     * Find stuff
     */
    public function findOneByFedKey($fedKey)
    {        
        if (!$fedKey) return null;
        
        $qb = $this->createQueryBuilder('personFed');
        
        $qb->select('person,personFed,personFedCert');
        
        $qb->leftJoin('personFed.person','person');
        $qb->leftJoin('personFed.certs','personFedCert');
        
        $qb->andWhere('personFed.fedKey = :fedKey');
        $qb->setParameter('fedKey', $fedKey);
        
      //$qb->orderBy('personTeam.projectKey,personTeam.levelKey,personTeam.teamDesc');
        
        $personFeds = $qb->getQuery()->getResult();
        
        return (count($personFeds) == 1) ?  $personFeds[0] : null;
    }
}
?>
