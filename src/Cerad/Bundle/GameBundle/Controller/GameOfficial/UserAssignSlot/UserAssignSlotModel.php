<?php
namespace Cerad\Bundle\GameBundle\Controller\GameOfficial\UserAssignSlot;

use Symfony\Component\HttpFoundation\ParameterBag;

use Symfony\Component\EventDispatcher\Event as PersonFindEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// Make my own exceptions
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Cerad\Bundle\PersonBundle\Events as PersonEvents;

use Cerad\Bundle\GameBundle\Events   as GameEvents;
use Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;

use Cerad\Bundle\GameBundle\Service\GameOfficial\AssignSlot\AssignSlotWorkflow as Workflow;

/* =======================================================
 * This model has dependencies from different bundles
 * Good argument for leaving it in the tourn bundle?
 */
class UserAssignSlotModel
{
    protected $dispatcher;
    
    public $userPerson;
    
    public $project;
    public $projectKey;
    
    public $slot;
    public $game;
    public $gameOfficial;
    public $gameOfficialClone;
        
    public $person;  // AKA Official
    public $persons; // AKA Officials
    
    public $valid = false;
    
    protected $gameRepo;
    
    public function __construct($project, $userPerson, $gameRepo)
    {   
        $this->userPerson = $userPerson;
        
        $this->project    = $project;
        $this->projectKey = $project->getKey();
        
        $this->gameRepo   = $gameRepo;
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
     */
    public function process()
    {   
        // See if the state was changed
        $gameOfficial      = $this->gameOfficial;
        $gameOfficialClone = $this->gameOfficialClone;
        
        $assignStateNew = $gameOfficial->getAssignState();
        $assignStateOld = $gameOfficialClone->getAssignState();
        
        if ($assignStateNew == $assignStateOld) return;
        
        
        // Tell the world about to change
        $eventPre = new AssignSlotEvent($gameOfficial,$gameOfficialClone);
        $this->dispatcher->dispatch(GameEvents::GAME_OFFICIAL_ASSIGN_SLOT__PRE, $eventPre);
        
        $person   = $this->person;
        $gameRepo = $this->gameRepo;
        
        // Need to dispatch an event when the state changes
        switch($assignStateNew)
        {
            case Workflow::StateRequested:
            case Workflow::StateIfNeeded:
                $gameOfficial->setAssignState($assignStateNew);
                $gameOfficial->setPersonGuid    ($person->getGuid());
                $gameOfficial->setPersonNameFull($person->getName()->full);
                break;
                
            case Workflow::StateRemove:
                $gameOfficial->setAssignState(Workflow::StateOpen);
                $gameOfficial->setPersonGuid    (null);
                $gameOfficial->setPersonNameFull(null);
                break;
                
            case Workflow::StateTurnback:
                $gameOfficial->setAssignState(Workflow::StateOpen);
                $gameOfficial->setPersonGuid    (null);
                $gameOfficial->setPersonNameFull(null);
                break;
        }
        // Tell the world changed
        $gameRepo->commit();
        $eventPost = new AssignSlotEvent($gameOfficial,$gameOfficialClone);
        $this->dispatcher->dispatch(GameEvents::GAME_OFFICIAL_ASSIGN_SLOT__POST, $eventPost);
        
        return;
    }
    /* =========================================================================
     * Also holds logic to allow signing up for this particular game slot?
     */
    public function create(ParameterBag $requestAttributes)
    {   
        // Extract
        $num  = $requestAttributes->get('game');
        $slot = $requestAttributes->get('slot');
        
        // Verify game exists
        $game = $this->gameRepo->findOneByProjectNum($this->projectKey,$num);
        if (!$game) {
            throw new NotFoundHttpException(sprintf('Game %d does not exist.',$num));
        }
        // Verify slot exists
        $gameOfficial = $game->getOfficialForSlot($slot);
        if (!$gameOfficial) {
            throw new NotFoundHttpException(sprintf('Game Slot %d,%id does not exist.',$num,$slot));
        }
        // Verify have a person
        //$personGuid = $this->user ? $this->user->getPersonGuid() : null;
        //$person = $this->findPersonByGuid($personGuid);
        $person = $this->userPerson;
        if (!$person) 
        {
            throw new AccessDeniedHttpException(sprintf('Game Slot %d,%id, has no person record.',$num,$slot));
        }
        if (!$gameOfficial->isUserAssignable()) {
            throw new AccessDeniedHttpException(sprintf('Game Slot %d,%id is not user assignable.',$num,$slot));
        }
        $gameOfficialClone = clone $gameOfficial;
        
        // Must be a referee
        $personPlan = $person->getPlan($this->projectKey,false);
        
        // This is okay for now
        if (!$gameOfficial->getPersonNameFull())
        {
            if ($personPlan)
            {
                $gameOfficial->setPersonNameFull($personPlan->getPersonName());
            }
        }
        /* =================================================
         * Enough checking for now
         * 
        $personNameFull = $person->getName()->full;
        
        // Already have someone signed up
        if ($gameOfficial->getPersonGuid())
        {
            // Okay - might want to request removal
            if ($gameOfficial->getPersonGuid() != $personGuid) return;
        }
        // Check for name?
        if ($gameOfficial->getPersonNameFull())
        {
            // Okay - might want to request removal
            if ($gameOfficial->getPersonNameFull() != $personNameFull) return;
        }
        // Make sure the person is a referee
        
        // Actually assign the person here?
        $gameOfficial->setPersonGuid    ($personGuid);
        $gameOfficial->setPersonNameFull($personNameFull);
        
        // Request assignment or request removal
        // Needs to be in SelfAssign workflow state
        if (!$gameOfficial->getState()) $gameOfficial->setState('Requested');
        */
        
        // Want to see if person is part of a group for this project
        $persons = array($person);
        
        // Xfer the data
        $this->slot = $slot;
        $this->game = $game;
        
        $this->gameOfficial      = $gameOfficial;
        $this->gameOfficialClone = $gameOfficialClone;
        
        $this->person  = $person;  // AKA Official
        $this->persons = $persons; // AKA Officials
        
        $this->valid = true;
        
        // Pretend I am a factory
        return $this;
    }
}
