<?php
namespace Cerad\Bundle\ProjectBundle\InMemory;

use Symfony\Component\Yaml\Yaml;

use Cerad\Bundle\ProjectBundle\Model\Project;
use Cerad\Bundle\ProjectBundle\Model\ProjectRepositoryInterface;

class ProjectRepository implements ProjectRepositoryInterface
{
    protected $projects;
    
    public function __construct($files)
    {
        $projects = array();
        foreach($files as $file)
        {
            $configs = Yaml::parse(file_get_contents($file));
            
            foreach($configs as $config)
            {
                $project = new Project($config);
                $projects[$project->getId()] = $project;
            }
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
            foreach($project->getSlugs() as $slugx)
            {
                if ($slug == $slugx) return $project;
            }
        }
        return null;
    }
}

?>
