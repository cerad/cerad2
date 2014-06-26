<?php
namespace Cerad\Bundle\GameBundle\Doctrine\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;

/* =====================================================
 * Probably don't really need this
 */
class GameOfficialRepository extends EntityRepository
{   
    public function findAllByProjectPerson($projectPerson)
    {
        $personKey  = $projectPerson->getPerson()->getKey();
        $projectKey = $projectPerson->getProjectKey();
        
        $qb = $this->createQueryBuilder('gameOfficial');
        
        $qb->addSelect('game');
        $qb->leftJoin ('gameOfficial.game','game');
        
        $qb->andWhere('gameOfficial.personGuid = :personKey');
        $qb->setParameter('personKey',$personKey);
        
        $qb->andWhere('game.projectKey = :projectKey');
        $qb->setParameter('projectKey', $projectKey);
         
        return $qb->getQuery()->getResult();
         
    }
}
?>
