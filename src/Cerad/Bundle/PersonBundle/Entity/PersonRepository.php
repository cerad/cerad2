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
        if (!$id) return null;
        return parent::find($id);
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
}
?>
