<?php
namespace Cerad\Bundle\GameBundle\Controller\GameOfficial\UserAssignSlot;

use Symfony\Component\HttpFoundation\ParameterBag;

use Symfony\Component\EventDispatcher\Event as PersonFindEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// Make my own exceptions?
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Exception\AccessDeniedException;

use Cerad\Bundle\PersonBundle\Events as PersonEvents;

//  Cerad\Bundle\GameBundle\Events   as GameEvents;
//  Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;

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
        
    public $person;  // AKA Official
    public $persons; // AKA Officials
    
    public $valid = false;
    
    protected $gameRepo;
    protected $workflow;
    
    public function __construct($project, $userPerson, $gameRepo, Workflow $workflow)
    {   
        $this->userPerson = $userPerson;
        
        $this->project    = $project;
        $this->projectKey = $project->getKey();
        
        $this->gameRepo = $gameRepo;
        $this->workflow = $workflow;
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
        // Like an internal clone
        $gameOfficial->saveOriginalInfo();
        
        // Verify have a person
        //$personGuid = $this->user ? $this->user->getPersonGuid() : null;
        //$person = $this->findPersonByGuid($personGuid);
        $person = $this->userPerson;
        if (!$person) 
        {
            throw new AccessDeniedException(sprintf('Game Slot %d,%id, has no person record.',$num,$slot));
        }
        if (!$gameOfficial->isUserAssignable()) {
            throw new AccessDeniedException(sprintf('Game Slot %d,%id is not user assignable.',$num,$slot));
        }
        // Must be a referee
        $personPlan = $person->getPlan($this->projectKey,false);
        if (!$personPlan) 
        {
            throw new AccessDeniedException(sprintf('Game Slot %d,%id, has no person plan record.',$num,$slot));
        }
        
        // This should be okay and makes the single slot request form more usable
        if (!$gameOfficial->getPersonNameFull())
        {
            $gameOfficial->setPersonNameFull($personPlan->getPersonName());
        }
        
        // Want to see if person is part of a group for this project
        $persons = array($person);
        
        // Xfer the data
        $this->slot = $slot;
        $this->game = $game;
        
        $this->gameOfficial = $gameOfficial;
        
        $this->person     = $person;  // AKA Official
        $this->personPlan = $personPlan;
        
        $this->persons = $persons; // AKA Officials
        
        $this->valid = true;
        
        // Pretend I am a factory
        return $this;
    }
}
