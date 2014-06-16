<?php

namespace Cerad\Bundle\CoreBundle\Event\Team;

use Symfony\Component\EventDispatcher\Event;

class ChangedTeamEvent extends Event
{
    const Changed  = 'CeradTeamChangedTeamEvent';
    
    protected $team;
    protected $groupSlot;
    
    public function __construct($team,$groupSlot = null)
    {
        $this->team      = $team;
        $this->groupSlot = $groupSlot;
    }
    public function getTeam()      { return $this->team; }
    public function getGroupSlot() { return $this->groupSlot; }
}