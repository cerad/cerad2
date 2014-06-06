<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FindOfficialsEvent extends Event
{
    const FindOfficialsEventName = 'CeradPersonFindOfficials';
    
    protected $project;
    protected $officials = array();
    
    public function __construct($project,$game = null)
    {
        $this->game    = $game;
        $this->project = $project;
    }
    public function getOfficials()           { return $this->officials;     }
    public function setOfficials($officials) { $this->officials = $officials; }

    public function getGame()    { return $this->game;    }
    public function getProject() { return $this->project; }
}