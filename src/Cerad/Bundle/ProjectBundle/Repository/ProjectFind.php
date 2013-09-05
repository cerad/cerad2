<?php
namespace Cerad\Bundle\ProjectBundle\Repository;

use Cerad\Bundle\ProjectBundle\Model\ProjectRepositoryInterface;

/* ======================================================
 * Finds one project by id
 * Used for selecting a default tournament project
 */
class ProjectFind
{
    public $project;
    public function __construct(ProjectRepositoryInterface $repo,$id)
    {
        $this->project = $repo->find($id);
    }
}
?>
