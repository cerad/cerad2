<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\Assign;

use Cerad\Bundle\GameBundle\GameEvents;
use Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;

/* =========================================================
 * TODO: Should be possible to have project specific workflows?
 * 
 * User and Asignee shar the same workflow
 */
class AssignByAssigneeWorkflow extends AssignWorkflow
{
    public function getStateOptions($state, $transitions = null)
    {
        return parent::getStateOptions($state,$this->assigneeStateTransitions);
        if ($transitions);
    }
    public function process($gameOfficial,$projectOfficial)
    {
        $gameOfficialOrg = $gameOfficial->retrieveOriginalInfo();
        
        $assignStateNew = $this->mapPostedStateToInternalState($gameOfficial->getAssignState());
        $assignStateOrg = $this->mapPostedStateToInternalState($gameOfficialOrg['assignState']);
        
        if ($assignStateNew == $assignStateOrg) 
        {
            // Reset orginal info
            $gameOfficial->restoreOriginalInfo();
            return;
        }
        $transition = $this->assigneeStateTransitions[$assignStateOrg][$assignStateNew];
        
        // Normally go directly to new state but sometimes want a different state
        $assignStateMod = isset($transition['modState']) ? $transition['modState'] : $assignStateNew;
        if ($assignStateMod != $assignStateNew)
        {
            $gameOfficial->setAssignState($this->mapInternalStateToPostedState($assignStateMod));
        }
        // Transfer or clear person
        switch($assignStateMod)
        {
            case 'StateOpen':
                $gameOfficial->setPersonFromPlan(null);
                break;
            default:
                $gameOfficial->setPersonFromPlan($projectOfficial);
        }
        // Notify the world
        $event = new AssignSlotEvent;
        $event->gameOfficial    = $gameOfficial;
        $event->gameOfficialOrg = $gameOfficialOrg;
        $event->command         = $assignStateNew;
        $event->workflow        = $this;
        $event->transition      = $transition;
        $event->by              = 'Assignee';
        
        $this->dispatcher->dispatch(GameEvents::GameOfficialAssignSlot,$event);
    }
}