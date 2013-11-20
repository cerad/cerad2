<?php

namespace Cerad\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

// Even though the class name is available through the manager
use Cerad\Bundle\UserBundle\Model\UserInterface;
use Cerad\Bundle\UserBundle\Model\UserRepositoryInterface;

use Cerad\Bundle\UserBundle\Entity\User as UserEntity;

/* ============================================
 * Going with a simple extends here
 * FOSUser actually injects an object manager and then wraps the relavant methods
 * Could be a refactor for later
 */
class UserRepository extends EntityRepository implements UserRepositoryInterface
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
    function createUser($params = null) { return new UserEntity($params); }
    
    /* ==========================================================
     * Persistence
     * 
     * Note that clear is already implemented and uses person as a root entity
     */
    public function save(UserInterface $entity)
    {
        if (!($entity instanceOf UserEntity)) throw new \Exception('Tried to persist invalid entity type');
        
        $em = $this->getEntityManager();

        return $em->persist($entity);
    }
    public function commit()
    {
       $em = $this->getEntityManager();
       return $em->flush();
    }
    public function delete(UserInterface $entity)
    {
        if (!($entity instanceof UserEntity)) 
        {
            throw new \Exception('Wrong type of entity for remove');
        }
        $em = $this->getEntityManager();

        return $em->remove($entity);
    }
    /* =========================================================================
     * Still a bit uneasy about user repo vs user manager
     */
    public function findOneByPersonGuid($guid)
    {
        if (!$guid) return null;
        
        return $this->findOneBy(array('personGuid' => $guid));
    }
}
?>
