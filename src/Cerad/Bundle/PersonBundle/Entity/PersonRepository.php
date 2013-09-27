<?php

namespace Cerad\Bundle\PersonBundle\Entity;

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
    public function findByFed($id)
    {
        if (!$id) return null;
        
        $repo = $this->_em->getRepository('CeradPersonBundle:PersonFed');
        
        $fed = $repo->find($id); 
        
        if ($fed) return $fed->getPerson();
        
        return null;
    }
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
    public function findPersonPerson($id)
    {
        if (!$id) return null;
        $repo = $this->_em->getRepository('CeradPersonBundle:PersonPerson');
        return $repo->find($id);        
    }
    /* ==========================================================
     * Allow creating objects via static methods
     */
    static function createPerson($params = null) { return new PersonEntity($params); }
    
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
    
    public function truncate()
    {
        $conn = $this->_em->getConnection();
        $conn->executeUpdate('DELETE FROM person_persons;');
        $conn->executeUpdate('DELETE FROM person_plans;'  );
        $conn->executeUpdate('DELETE FROM persons;'       );
        
        $conn->executeUpdate('ALTER TABLE person_persons AUTO_INCREMENT = 1;');
        $conn->executeUpdate('ALTER TABLE person_plans   AUTO_INCREMENT = 1;');
        $conn->executeUpdate('ALTER TABLE persons        AUTO_INCREMENT = 1;');        
    }
}
?>
