<?php
namespace Cerad\Bundle\TournBundle\Controller\Schedule;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\FormType\Schedule\Official\SelfAssignSlotFormType;

class ScheduleOfficialSelfAssignModel
{
    public $user;
    public $project;
    public $projectKey;
    
    public $slot;
    public $game;
    public $gameOfficial;
        
    public $person;  // AKA Official
    public $persons; // AKA Officials
    
    public $valid = false;
    
    protected $gameRepo;
    protected $personRepo;
    
    public function __construct($request, $project, $user, $personRepo, $gameRepo)
    {   
        $this->user = $user;
        
        $this->project    = $project;
        $this->projectKey = $project->getKey();
        
        $this->gameRepo   = $gameRepo;
        $this->personRepo = $personRepo;
        
        $this->create($request);
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
        $game = $this->gameRepo->findOneByProjectNum($this->projectKey,$request->get('game'));
        if (!$game) return;
        
        // Make sure the slot can be assigned
        $slot = $request->get('slot');
        $gameOfficial = $game->getOfficialForSlot($slot);
        if (!$gameOfficial);
        if (!$gameOfficial->isUserAssignable()) return;

        // Must have a person
        $personGuid = $this->user ? $this->user->getPersonGuid() : null;
        $person = $this->personRepo->findOneByGuid($personGuid);
        if (!$person) return;
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
        
        // Want to see if person is part of a group for this project
        $persons = array($person);
        
        // Xfer the data
        $this->slot         = $slot;
        $this->game         = $game;
        $this->gameOfficial = $gameOfficial;
        
        $this->person  = $person;  // AKA Official
        $this->persons = $persons; // AKA Officials
        
        $this->valid = true;
    }
    public function createForm()
    {
        $game = $this->game;
        $slot = $this->slot;
        
        $builder = $this->helper->createFormBuilder($this);
        
        $builder->setAction($this->helper->generateUrl($this->route,
            array('game' => $game->getNum(),'slot' => $slot)
        ));
      //$builder->setMethod('POST'); // default
        
      //$builder->add('slots','collection',array('type' => new SelfAssignSlotFormType($model['officials'])));
        
        $builder->add('gameOfficial',new SelfAssignSlotFormType());
        
        $builder->add('assign', 'submit', array(
            'label' => 'Request Assignment',
            'attr' => array('class' => 'submit'),
        ));        
         
        return $builder->getForm();
    }
}
