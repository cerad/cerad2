<?php
namespace Cerad\Bundle\GameBundle\Event\GameOfficial;

use Symfony\Component\EventDispatcher\Event;

class AssignSlotEvent extends Event
{
    public $gameOfficialNew;
    public $gameOfficialOld;
    
    public function __construct($gameOfficialNew,$gameOfficialOld)
    {
        $this->gameOfficialNew = $gameOfficialNew;
        $this->gameOfficialOld = $gameOfficialOld;
    }
}
