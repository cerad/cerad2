<?php
namespace Cerad\Bundle\ProjectBundle\InMemory;

use Cerad\Bundle\ProjectBundle\Model\ProjectFindInterface;
use Cerad\Bundle\ProjectBundle\Model\ProjectRepositoryInterface;

/* ======================================================
 * Finds one project by id
 * Used for selecting a default tournament project
 * By creating a service an injecting the project id
 */
class ProjectFind implements ProjectFindInterface
{
    public $project;
    
    public function __construct(ProjectRepositoryInterface $repo, $id)
    {
        $this->project = $repo->find($id);
    }
}
?>
