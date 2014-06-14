<?php

namespace Cerad\Bundle\CoreBundle\Event\Game;

use Symfony\Component\EventDispatcher\Event;

class UpdatedGameReportEvent extends Event
{
    const Updated  = 'CeradGameUpdatedGameReport';
    
    protected $game;
    
    public function __construct($game)
    {
        $this->game = $game;
    }
    public function getGame() { return $this->game; }
}