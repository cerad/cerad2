<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

// TODO: Project or Projects?
// TODO: Maybe criteria might be better
// A level bundle event is now available so delete this.
class FindProjectLevelsEvent extends Event
{
    const FindProjectLevels = 'CeradProjectFindProjectLevels';
    
    protected $projectKey;
    protected $programs;
    protected $genders;
    protected $ages;
    
    protected $levelKeys = array();
    
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

    public function getLevelKeys() { return $this->levelKeys; }
    
    public function setLevelKeys($keys) { $this->levelKeys = $keys; }
}