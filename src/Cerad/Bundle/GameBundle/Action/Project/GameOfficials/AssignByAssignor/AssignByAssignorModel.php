<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\Event as PersonFindEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Cerad\Bundle\PersonBundle\PersonEvents;

use Cerad\Bundle\GameBundle\Action\Project\GameOfficials\Assign\AssignWorkflow;

/* =======================================================
 * This model has dependencies from different bundles
 * Good argument for leaving it in the tourn bundle?
 */
class AssignByAssignorModel
{
    protected $dispatcher;
    
    public $game;
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
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
        
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
                $event = new PersonFindEvent;
                $event->project    = $this->project;
                $event->personGuid = $personGuid;
                $event->personPlan = null;
        
                $this->dispatcher->dispatch(PersonEvents::FindPersonPlanByProjectAndPersonGuid,$event);

                $projectOfficial = $event->personPlan;
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
        $event = new PersonFindEvent;
        $event->project   = $project;
        $event->officials = array();
        
        $this->dispatcher->dispatch(PersonEvents::FindOfficialsByProject,$event);

        $this->projectOfficials = $event->officials;
        
        return $this;
    }
}