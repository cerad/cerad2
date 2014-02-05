<?php
namespace Cerad\Bundle\TournBundle\Controller\GameOfficial;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/* =======================================================
 * This model has dependencies from different bundles
 * Good argument for leaving it in the tourn bundle?
 */
class GameOfficialUserAssignSlotModel
{
    public $user;
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
    protected $personRepo;
    
    public function __construct($project, $user, $personRepo, $gameRepo)
    {   
        $this->user = $user;
        
        $this->project    = $project;
        $this->projectKey = $project->getKey();
        
        $this->gameRepo   = $gameRepo;
        $this->personRepo = $personRepo;
    }
    /* =====================================================
     * Process a posted model
     */
    public function processModel($model)
    {   
        die('process model');
        
        $project = $model['project'];
        $projectId = $project->getId();
        
        $personRepo = $this->get('cerad_person.person_repository');
         
        // Should point to original slots
        $slots = $model['slots'];
        foreach($slots as $slot)
        {
            $personGuid = $slot->getPersonGuid();
            if ($personGuid)
            {
                $person = $personRepo->findOneByGuid($personGuid);
                if ($person)
                {
                    $name = $person->getName();
                    $slot->setPersonNameFull($name->full);
                }
            }
            else
            {
                $person = $personRepo->findOneByProjectName($projectId,$slot->getPersonNameFull());
                $personGuid = $person ? $person->getGuid() : null;
                $slot->setPersonGuid($personGuid);
            }
        }
        // Lots to add
        $gameRepo = $this->get('cerad_game.game_repository');
        $gameRepo->commit();
        
    }
    /* =========================================================================
     * Also holds logic to allow signing up for this particular game slot?
     */
    public function create(Request $request)
    {   
        // Need game
        $num  = $request->attributes->get('game');
        $game = $this->gameRepo->findOneByProjectNum($this->projectKey,$num);
        if (!$game) {
            throw new NotFoundHttpException(sprintf('Game %d does not exist.',$num));
        }
        // Make sure the slot can be assigned
        $slot = $request->attributes->get('slot');
        $gameOfficial = $game->getOfficialForSlot($slot);
        if (!$gameOfficial) {
            throw new NotFoundHttpException(sprintf('Game Slot %d,%id does not exist.',$num,$slot));
        }
        if (!$gameOfficial->isUserAssignable()) {
            throw new AccessDeniedHttpException(sprintf('Game Slot %d,%id is not user assignable.',$num,$slot));
        }
        $gameOfficialClone = clone $gameOfficial;
      //if ($gameOfficial->getAssignState() == 'Open') $gameOfficial->setAssignState('Requested');
        
        // Must have a person
        $personGuid = $this->user ? $this->user->getPersonGuid() : null;
        $person = $this->personRepo->findOneByGuid($personGuid);
        if (!$person) 
        {
            throw new AccessDeniedHttpException(sprintf('Game Slot %d,%id, has no person record.',$num,$slot));
        }
        // Must be a referee
        
        
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
    }
}
