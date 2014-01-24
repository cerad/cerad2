<?php
namespace Cerad\Bundle\ProjectBundle\InMemory;

use Cerad\Bundle\ProjectBundle\Model\ProjectFindInterface;
use Cerad\Bundle\ProjectBundle\Model\ProjectRepositoryInterface;

/* ======================================================
 * Factory class for getting the current project
 */
class ProjectCurrent implements ProjectFindInterface
{
    static public function get(ProjectRepositoryInterface $repo, $id)
    {
        return $repo->find($id);
    }
}
?>
