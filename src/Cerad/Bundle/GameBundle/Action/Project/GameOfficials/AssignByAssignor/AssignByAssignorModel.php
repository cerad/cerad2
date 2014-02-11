<?php
namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\Event as PersonFindEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Cerad\Bundle\PersonBundle\PersonEvents;

//  Cerad\Bundle\GameBundle\Events   as GameEvents;
//  Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;

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
    
    public $projectOfficials;
    public $projectOfficialsOptions;
    
    protected $gameRepo;
    protected $workflow;
    
    public function __construct(AssignWorkflow $workflow, $gameRepo)
    {   
        $this->workflow = $workflow;
        $this->gameRepo = $gameRepo;
    }
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    protected function findPersonByGuid($guid)
    {
        if (!$guid) return null;
        
        $event = new PersonFindEvent;
        $event->guid   = $guid;
        $event->person = null;
        
        $this->dispatcher->dispatch(PersonEvents::FindPersonByGuid,$event);
        
        return $event->person;
    }
    
    /* =====================================================
     * Process a posted model
     * Turn everything over to the workflow
     */
    public function process()
    {   
        $this->workflow->processPostByAssignee($this->gameOfficial,$this->personPlan);
        $this->gameRepo->commit();
        return;
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
        
        foreach($gameOfficials as $gameOfficial)
        {
            // Like an internal clone
            $gameOfficial->saveOriginalInfo();
        }
        
        // List of available referees
        $event = new PersonFindEvent;
        $event->project   = $project;
        $event->officials = array();
        
        $this->dispatcher->dispatch(PersonEvents::FindOfficialsByProject,$event);

        $this->projectOfficials = $projectOfficials = $event->officials;
        
        // Not sure if this really belongs here but it helps
        $options = array();
        foreach($projectOfficials as $projectOfficial)
        {
            $plan = $projectOfficial->getPlan();
            $options[$projectOfficial->getGuid()] = $plan->getPersonName();
        }
        print_r($options); die();
        $this->projectOfficialsOptions = $options;
        
        return $this;
    }
}