<?php
namespace Cerad\Bundle\GameBundle\EntityRepository;

use Doctrine\ORM\EntityRepository;

/* =====================================================
 * Probably don't really need this
 */
class GameOfficialRepository extends EntityRepository
{   
    public function find($id)
    {
        return $id ? parent::find($id) : null;
    }
    public function findOneByProjectName($projectId, $name)
    {
        return $this->findOneBy(array('projectId' => $projectId, 'name' => $name));    
    }
    public function findByProject($projectId)
    {
        return $this->findBy(array('projectId' => $projectId),array('id','slot'));    
    }

}
?>
