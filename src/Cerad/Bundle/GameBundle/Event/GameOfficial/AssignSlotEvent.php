<?php
namespace Cerad\Bundle\GameBundle\Event\GameOfficial;

use Symfony\Component\EventDispatcher\Event;

class AssignSlotEvent extends Event
{
    public $project;
    public $gameOfficial;
    public $gameOfficialOrg; // Original state, name, guid
    public $command;         // Turnback decline
    public $workflow;        // Or maybe transition
    public $transition;
    public $by;              // Assignor or Assignee
}
