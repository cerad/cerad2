<?php

namespace Cerad\Bundle\OrgBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Cerad\Bundle\OrgBundle\Entity\Org as OrgEntity;

class OrgRepository extends EntityRepository
{   
    public function createOrg($config = null) { return new OrgEntity($config); }

    public function find($id)
    {
        return $id ? parent::find($id) : null;
    }

    /* ==========================================================
     * Persistence
     */
    public function save($entity)
    {
        if ($entity instanceof OrgEntity) 
        {
            $em = $this->getEntityManager();

            return $em->persist($entity);
        }
        throw new \Exception('Wrong type of entity for orgRepo save');
    }
    public function commit()
    {
       $em = $this->getEntityManager();
       return $em->flush();
    }
}
?>
