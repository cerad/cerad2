<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\Assign;

class AssignByAssignorWorkflow extends AssignWorkflow
{
    public function getStateOptions($state, $options = null)
    {
        return parent::getStateOptions($state,$this->assignorStateTransitions);
        if ($options);
    }
    public function process($project,$gameOfficialOrg,$gameOfficialNew,$personPlan)
    {
        $assignStateNew = $this->mapPostedStateToInternalState($gameOfficialNew->getAssignState());
        $assignStateOrg = $this->mapPostedStateToInternalState($gameOfficialOrg->getAssignState());
        
       // The assignor can type directly into the name
        $personNameNew = $gameOfficialNew->getPersonNameFull();
        $personNameOrg = $gameOfficialOrg->getPersonNameFull();
        
        if ($personNameNew != $personNameOrg)
        {
            $gameOfficialNew->setPersonFromPlan(null);
            $gameOfficialNew->setPersonNameFull($personNameNew);
            
            // Bypass all the state checks etc for now
            // Could check to see if the name was unique and link it
            return;
        }
        if ($assignStateNew == $assignStateOrg) return;
        
        $transition = $this->assignorStateTransitions[$assignStateOrg][$assignStateNew];
        
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
                $gameOfficialNew->setPersonFromPlan($personPlan);
        }
        // Should we notify the assiignee
        $notifyAssignee = isset($transition['notifyAssignee']) ? true : false;
        
        if (!$notifyAssignee) return;
        
        // TBD - Kick off notification
    }
}