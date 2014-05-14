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
    /* =========================
     * Returns false if unchanged
     */
    public function process($project,$gameOfficialOrg,$gameOfficialNew,$projectOfficial)
    {   
        $assignStateNew = $this->mapPostedStateToInternalState($gameOfficialNew->getAssignState());
        $assignStateOrg = $this->mapPostedStateToInternalState($gameOfficialOrg->getAssignState());
        
        if ($assignStateNew == $assignStateOrg) 
        {
            // Reset orginal info
            // $gameOfficial->restoreOriginalInfo();
            return false;
        }
        $transition = $this->assigneeStateTransitions[$assignStateOrg][$assignStateNew];
        
        // Normally go directly to new state but sometimes want a different state
        $assignStateMod = isset($transition['modState']) ? $transition['modState'] : $assignStateNew;
        if ($assignStateMod != $assignStateNew)
        {
            $gameOfficialNew->setAssignState($this->mapInternalStateToPostedState($assignStateMod));
        }
        // Transfer or clear person
        switch($assignStateMod)
        {
            case 'StateOpen':
                $gameOfficialNew->setPersonFromPlan(null);
                break;
            default:
                $gameOfficialNew->setPersonFromPlan($projectOfficial);
        }
        // Notify the world
        $event = new AssignSlotEvent;
        $event->project         = $project;
        $event->gameOfficial    = $gameOfficialNew;
        $event->gameOfficialOrg = $gameOfficialOrg;
        $event->command         = $assignStateNew;
        $event->workflow        = $this;
        $event->transition      = $transition;
        $event->by              = 'Assignee';
        
        $this->dispatcher->dispatch(GameEvents::GameOfficialAssignSlot,$event);
        
        return true;
    }
}