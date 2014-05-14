<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\CoreBundle\Event\FindOfficialsEvent;
use Cerad\Bundle\CoreBundle\Event\FindPersonPlanEvent;

use Cerad\Bundle\GameBundle\Action\Project\GameOfficials\Assign\AssignWorkflow;

class AssignByAssignorModel extends ActionModelFactory
{   
    public $game;
    public $back;
    public $gameOfficials;
    public $gameOfficialClones;
    
    public $projectOfficials;
    
    public $workflow;
    
    protected $gameRepo;
    
    public function __construct(AssignWorkflow $workflow, $gameRepo)
    {   
        $this->workflow = $workflow;
        $this->gameRepo = $gameRepo;
    }
        
    /* =====================================================
     * Process a posted model
     * Turn everything over to the workflow
     */
    public function process()
    {   
        foreach($this->gameOfficials as $gameOfficial)
        {
            $personGuid = $gameOfficial->getPersonGuid();
            if ($personGuid)
            {
                $event = new FindPersonPlanEvent($this->project,$personGuid);
        
                $this->dispatcher->dispatch(FindPersonPlanEvent::FindByProjectGuidEventName,$event);

                $projectOfficial = $event->getPlan();
            }
            else $projectOfficial = null; // Ok if only name was set
            
            // All the real majic happens here
            $gameOfficialClone = $this->gameOfficialClones[$gameOfficial->getSlot()];
            $this->workflow->process($this->project,$gameOfficialClone,$gameOfficial,$projectOfficial);
            
            // Possibly restore to original values?
            
        }
        $this->gameRepo->commit();
    }
    /* =========================================================================
     * Also holds logic to allow signing up for this particular game slot?
     */
    public function create(Request $request)
    {   
        // Extract
        $this->back = $request->query->get('back');

        $requestAttrs = $request->attributes;
        
        $this->project       = $project = $requestAttrs->get('project');
        $this->game          = $game    = $requestAttrs->get('game');
        $this->gameOfficials = $gameOfficials = $game->getOfficials();
        
        $this->gameOfficialsOrg = array();
        
        foreach($gameOfficials as $gameOfficial)
        {
            // Like an internal clone
            $this->gameOfficialClones[$gameOfficial->getSlot()] = clone $gameOfficial;
        }
        
        // List of available referees
        $event = new FindOfficialsEvent($project);
        
        $this->dispatcher->dispatch(FindOfficialsEvent::FindOfficialsEventName,$event);

        $this->projectOfficials = $event->getOfficials();
        
        return $this;
    }
}