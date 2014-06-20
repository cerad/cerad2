<?php

namespace Cerad\Bundle\CoreBundle\Event\Level;

use Symfony\Component\EventDispatcher\Event;

class FindProjectLevelsEvent extends Event
{
    const Find = 'CeradLevelFindProjectLevels';
    
    protected $project;
    protected $programs;
    protected $genders;
    protected $ages;
    
    protected $levels = array();
    
    public function __construct($project,$programs,$genders,$ages)
    {
        $this->ages     = $ages;
        $this->genders  = $genders;
        $this->programs = $programs;
        $this->project  = $project;
    }
    public function getAges    () { return $this->ages;     }
    public function getGenders () { return $this->genders;  }
    public function getPrograms() { return $this->programs; }
    public function getProject () { return $this->project;  }

    public function setLevels($levels) { $this->levels = $levels; }
    
    public function getLevels()  { return $this->levels;  }
    
    public function getLevelKeys()
    {
        $levelKeys = array();
        foreach($this->levels as $level)
        {
            $levelKeys[] = $level->getKey();
        }
        return $levelKeys;
    }
}