<?php
namespace Cerad\Bundle\ProjectBundle\Repository;

use Cerad\Bundle\ProjectBundle\Model\Project;
use Cerad\Bundle\ProjectBundle\Model\ProjectRepositoryInterface;

class ProjectInMemoryRepository implements ProjectRepositoryInterface
{
    protected $projects;
    
    public function __construct($configs)
    {
        $projects = array();
        foreach($configs as $config)
        {
            $project = new Project($config);
            $projects[$project->getId()] = $project;
        }
        $this->projects = $projects;
    }
    public function find($id)
    {
        return isset($this->projects[$id]) ? $this->projects[$id] : null;
    }
    public function findAll()
    {
        return $this->projects;        
    }
    public function findAllByStatus($status)
    {
        $projects = array();
        foreach($this->projects as $project)
        {
            if ($status == $project->getStatus()) $projects[$project->getId()] = $project;
        }
        return $projects;
    }
    public function findBySlug($slug)
    {
        foreach($this->projects as $project)
        {
            if ($slug == $project->getSlug())  return $project;
            if ($slug == $project->getSlugx()) return $project;
        }
        return null;
    }
}

?>
