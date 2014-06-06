<?php

namespace Cerad\Bundle\PersonBundle\EntityRepository;

use Doctrine\ORM\EntityRepository;

// Even though the class name is available through the manager
use Cerad\Bundle\PersonBundle\Model\PersonRepositoryInterface;

use Cerad\Bundle\PersonBundle\Model \Person as PersonModel;
use Cerad\Bundle\PersonBundle\Entity\Person as PersonEntity;

/* ============================================
 * Going with a simple extends here
 * FOSUser actually injects an object manager and then wraps the relavant methods
 * Could be a refactor for later
 */
class PersonRepository extends EntityRepository implements PersonRepositoryInterface
{
    /* ==========================================================
     * Find stuff
     */
    public function find($id)
    {
        return $id ? parent::find($id) : null;
    }
    public function findOneByGuid($id)
    {
        if (!$id) return null;
        
        return $this->findOneBy(array('guid' => $id));
    }
    public function query($projects = null)
    {
        $qb = $this->createQueryBuilder('person');
        
        $qb->addSelect('personPlan');
        $qb->leftJoin ('person.plans','personPlan');
        
        if ($projects)
        {
            $qb->andWhere('personPlan.projectId IN (:projectIds)');
            $qb->setParameter('projectIds',$projects);
        }
        $qb->orderBy('person.nameLast,person.nameFirst');
        
        return $qb->getQuery()->getResult();
    }
    /* ====================================================
     * Grabs everyone for a project then filters for officials
     */
    public function findOfficialsByProject($projectKey, $program = null)
    {
        $qb = $this->createQueryBuilder('person');
        
        $qb->addSelect('personPlan');
        $qb->leftJoin ('person.plans','personPlan');
        
        $qb->andWhere('personPlan.projectId IN (:projectKey)');
        $qb->setParameter('projectKey',$projectKey);
 
        $qb->orderBy('personPlan.personName');
        
        $persons = $qb->getQuery()->getResult();
        $officials = array();
        foreach($persons as $person)
        {
            $selected = false;
            
            $personPlan = $person->getPlan();
            
            if ($personPlan->isOfficial()) $selected = true;
            
            if ($selected && $program)
            {
                $personPlanProgram = $personPlan->getProgram();
                switch($personPlanProgram)
                {
                    case null:    break;  // Is this legal?
                    case 'other': break;
                    default:
                        if ($program != $personPlanProgram) $selected = false;
                }
            }
            
            if ($selected) $officials[] = $person;
        }
        return $officials;
    }
    /* ===========================================================
     * Looking up person for a project by their full name
     * Take into account the possibility that there might be two people with the same name
     */
    public function findOneByProjectName($projectId,$personName)
    {
        if (!$personName) return null;
        
        $qb = $this->createQueryBuilder('person');
        
        $qb->addSelect('personPlan');
        $qb->leftJoin ('person.plans','personPlan');
        
        $qb->andWhere('person.nameFull = :personName');
        $qb->andWhere('personPlan.projectId  = :projectId' );
        
        $qb->setParameter('personName',$personName);
        $qb->setParameter('projectId', $projectId);
        
        $items = $qb->getQuery()->getResult();
        if (count($items) == 1) return $items[0];
        
        return null;
    }
    /* ==========================================================
     * Returns the person doe the fedId
     */
    public function findOneByFedKey($fedKey)
    {   
        $fed = $this->findFedByFedKey($fedKey);
        
        if ($fed) return $fed->getPerson();
        
        return null;
    }
    /* ================================================
     * Load record based on fedId AYSOV12341234
     */
    public function findFedByFedKey($fedKey)
    {
        if (!$fedKey) return null;
        $repo = $this->_em->getRepository('CeradPersonBundle:PersonFed');
        return $repo->findOneBy(array('fedKey' => $fedKey));
    }
    /* =================================================================
     * The next three load a record by id
     * Could probably be named better, used for updates
     */
    public function findFed($id)
    {
        if (!$id) return null;
        $repo = $this->_em->getRepository('CeradPersonBundle:PersonFed');
        return $repo->find($id);          
    }
    public function findPlan($id)
    {
        if (!$id) return null;
        $repo = $this->_em->getRepository('CeradPersonBundle:PersonPlan');
        return $repo->find($id);        
    }
    // TODO: Maybe allow personGuid to be an array
    public function findOnePersonPlanByProjectAndPersonGuid($projectKey,$personGuid)
    {
        if (!$personGuid) return null;
        
        if (is_object($projectKey)) $projectKey = $projectKey->getKey();
        
        $repo = $this->_em->getRepository('CeradPersonBundle:PersonPlan');
        
        $qb = $repo->createQueryBuilder('personPlan');
        
        $qb->addSelect('person');
        $qb->leftJoin ('personPlan.person','person');
        
        $qb->andWhere('person.guid = :personGuid');
        $qb->andWhere('personPlan.projectId = :projectKey' );
        
        $qb->setParameter('personGuid',$personGuid);
        $qb->setParameter('projectKey',$projectKey);
        
        $items = $qb->getQuery()->getResult();
        if (count($items) == 1) return $items[0];
    }
    public function findOnePersonPlanByProjectAndPersonName($project,$personName)
    {
        if (!$personName) return null;
        
        $projectKey = is_object($project) ? $project->getKey() : $project;
        
        $repo = $this->_em->getRepository('CeradPersonBundle:PersonPlan');
        
        $qb = $repo->createQueryBuilder('personPlan');
        
        $qb->addSelect('person');
        $qb->leftJoin ('personPlan.person','person');
        
        $qb->andWhere('personPlan.personName = :personName');
        $qb->andWhere('personPlan.projectId  = :projectKey' );
        
        $qb->setParameter('personName',$personName);
        $qb->setParameter('projectKey',$projectKey);
        
        $items = $qb->getQuery()->getResult();
        if (count($items) == 1) return $items[0];
    }
    public function findPersonPerson($id)
    {
        if (!$id) return null;
        $repo = $this->_em->getRepository('CeradPersonBundle:PersonPerson');
        return $repo->find($id);        
    }
    /* ==========================================================
     * Allow creating objects via static methods
     */
    function createPerson($params = null) { return new PersonEntity($params); }
    
    /* ==========================================================
     * Persistence
     * 
     * Note that clear is already implemented and uses person as a root entity
     */
    public function save(PersonModel $entity)
    {
        if ($entity instanceof PersonEntity) 
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
    public function delete(PersonModel $entity)
    {
        if (!($entity instanceof PersonEntity)) 
        {
            throw new \Exception('Wrong type of entity for remove');
        }
        $em = $this->getEntityManager();

        return $em->remove($entity);
    }
    public function truncate()
    {
        $conn = $this->_em->getConnection();
        $conn->executeUpdate('DELETE FROM person_fed_certs;' );
        $conn->executeUpdate('DELETE FROM person_fed_orgs;'  );
        $conn->executeUpdate('DELETE FROM person_feds;'      );
        
        $conn->executeUpdate('ALTER TABLE person_fed_certs AUTO_INCREMENT = 1;');
        $conn->executeUpdate('ALTER TABLE person_fed_orgs  AUTO_INCREMENT = 1;');
        
        $conn->executeUpdate('DELETE FROM person_persons;');
        $conn->executeUpdate('DELETE FROM person_plans;'  );
        $conn->executeUpdate('DELETE FROM persons;'       );
        
        $conn->executeUpdate('ALTER TABLE person_persons AUTO_INCREMENT = 1;');
        $conn->executeUpdate('ALTER TABLE person_plans   AUTO_INCREMENT = 1;');
        $conn->executeUpdate('ALTER TABLE persons        AUTO_INCREMENT = 1;');        
    }
    /* ===============================================================
     * This should probably go in a manager or some place
     * Changing the fed id can be complicated at best
     * 
     * Some of this can go away once the database is refactored and 
     * no longer need to cascade id updates
     */
    public function changeFedId($oldFed,$newId,$commit = true)
    {
        // Make sure it realy needs changing
        if ($oldFed->getId() == $newId) return;
        
        // For now, newId cannot exist
        $fedx = $this->findFed($newId);
        if ($fedx) return;
        
        // Need a new fed and then transfer
        $newFed = new PersonFed();
        $newFed->setId($newId);
        $newFed->setFedRoleId($oldFed->getFedRoleId());
        
        // Connect person to new fed
        $person = $oldFed->getPerson();
        $person->removeFed($oldFed);
        $person->addFed   ($newFed);
      //$newFed->setPerson($person);
        
        // Connect certs and orgs to new fed
        foreach($oldFed->getCerts() as $cert)
        {
            $oldFed->removeCert($cert);
            $cert->setFed($newFed);
        }
        foreach($oldFed->getOrgs() as $org)
        {
            $oldFed->removeOrg($org);
            $org->setFed($newFed);
         }
        
        // Remove old fed
        $em = $this->getEntityManager();
        $em->remove ($oldFed);
      //$em->persist($newFed);
        if ($commit) $em->flush();
    }
}
?>
