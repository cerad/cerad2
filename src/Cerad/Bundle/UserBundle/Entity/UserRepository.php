<?php

namespace Cerad\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

// Even though the class name is available through the manager
use Cerad\Bundle\UserBundle\Model\UserRepositoryInterface;

use Cerad\Bundle\UserBundle\Entity\User as UserEntity;

/* ============================================
 * Going with a simple extends here
 * FOSUser actually injects an object manager and then wraps the relavant methods
 * Could be a refactor for later
 */
class UserRepositoryDoctrine extends EntityRepository implements UserRepositoryInterface
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
    static function createUser($params = null) { return new UserEntity($params); }
    
    /* ==========================================================
     * Persistence
     * 
     * Note that clear is already implemented and uses person as a root entity
     */
    public function save(UserEntity $entity)
    {
        $em = $this->getEntityManager();

        return $em->persist($entity);
    }
    public function commit()
    {
       $em = $this->getEntityManager();
       return $em->flush();
    }
}
?>
