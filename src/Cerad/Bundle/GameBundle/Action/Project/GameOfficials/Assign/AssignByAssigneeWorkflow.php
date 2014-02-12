<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\Assign;

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
        // Should we notify the assignor
        $notifyAssignor = isset($transition['notifyAssignor']) ? true : false;
        
        if (!$notifyAssignor) return;
        
        // Need to setup message to the notify assignor listener
        
    }
}