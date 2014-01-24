<?php
namespace Cerad\Bundle\TournBundle\Controller\Schedule;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\FormType\Schedule\Official\SelfAssignSlotFormType;

class ScheduleOfficialSelfAssignModel
{
    public $helper;
    
    public $route;     // route name
    public $redirect;  // redirect name
    public $template;  // namespaced file name
    public $request;
    
    protected $gameRepo;
    protected $personRepo;
    
    public $project;
    public $projectKey;
    
    public function __construct($helper, $projectFind, $personRepo, $gameRepo)
    {
        $this->helper = $helper;
        
        $this->project    = $projectFind->project;
        $this->projectKey = $this->project->getKey();
        
        $this->personRepo = $personRepo;
        
        $this->gameRepo   = $gameRepo;
    }
    /* =====================================================
     * Process a posted model
     */
    public function processModel($model)
    {   
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
        $this->request  = $request;
        $this->route    = $request->get('_route');
        $this->redirect = $request->get('_redirect');
        $this->template = $request->get('_template');
        
        // Now have the ability to actually return a response
        $response = $this->helper->generateRedirectResponse($this->redirect);
        
        $game = $this->gameRepo->findOneByProjectNum($this->projectKey,$request->get('game'));
        if (!$game) return $response;
        
        // Make sure the slot can be assigned
        $slot = $request->get('slot');
        $gameOfficial = $game->getOfficialForSlot($slot);
        if (!$gameOfficial) return $response;
        if (!$gameOfficial->isUserAssignable()) return $response;

        // Must have a person
        $user = $this->helper->getUser();
        $personGuid = $user ? $user->getPersonGuid() : null;
        $person = $this->personRepo->findOneByGuid($personGuid);
        if (!$person) return $response;
        $personNameFull = $person->getName()->full;
        
        // Already have someone signed up
        if ($gameOfficial->getPersonGuid())
        {
            // Okay - might want to request removal
            if ($gameOfficial->getPersonGuid() != $personGuid) return $response;
        }
        // Check for name?
        if ($gameOfficial->getPersonNameFull())
        {
            // Okay - might want to request removal
            if ($gameOfficial->getPersonNameFull() != $personNameFull) return $response;
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
