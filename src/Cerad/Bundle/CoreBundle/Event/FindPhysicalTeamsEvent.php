<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

// TODO: Maybe criteria might be better
//       Find by project,program,age,gender
class FindPhysicalTeamsEvent extends Event
{
    const FindPhysicalTeams = 'CeradGameFindPhysicalTeams';
    
    protected $projectKey;
    protected $levelKeys = array();
    
    protected $teams = array();
    
    public function __construct($project,$levelKeys = array())
    {
        $this->projectKey = is_object($project) ? $project->getKey() : $project;
        $this->levelKeys  = $levelKeys;
    }
    public function getProjectKey() { return $this->projectKey; }
    public function getLevelKeys()  { return $this->levelKeys;  }

    public function getTeams() { return $this->teams; }
    
    public function setTeams($teams) { $this->teams = $teams; }
}