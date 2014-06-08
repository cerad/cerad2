<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FindProjectTeamsEvent extends Event
{
    const FindProjectTeams = 'CeradGameFindProjectTeams';
    
    protected $projectKey;
    protected $programs;
    protected $genders;
    protected $ages;
    
    protected $teams = array();
    
    public function __construct($project,$programs=null,$genders=null,$ages=null)
    {
        $this->projectKey = is_object($project) ? $project->getKey() : $project;
        $this->programs   = $programs;
        $this->genders    = $genders;
        $this->ages       = $ages;
    }
    public function getProjectKey() { return $this->projectKey;  }
    public function getPrograms()   { return $this->programs; }
    public function getGenders()    { return $this->genders;  }
    public function getAges()       { return $this->ages;     }

    public function getTeams() { return $this->teams; }
    
    public function setTeams($teams) { $this->teams = $teams; }
}