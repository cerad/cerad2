<?php

namespace Cerad\Bundle\CoreBundle\Event\Team;

use Symfony\Component\EventDispatcher\Event;

class FindTeamEvent extends Event
{
    const ByKey = 'CeradTeamFindTeamByKey';
    
    protected $teamKey;
    
    protected $team = array();
    
    public function __construct($teamKey)
    {
        $this->teamKey = $teamKey;
    }
    public function getTeamKey() { return $this->teamKey; }

    public function getTeam() { return $this->team; }
    
    public function setTeam($team) { $this->team = $team; }
}