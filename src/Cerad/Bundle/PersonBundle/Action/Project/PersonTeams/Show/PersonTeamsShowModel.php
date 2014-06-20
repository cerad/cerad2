<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonTeams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\CoreBundle\Event\Team\FindTeamEvent;

class PersonTeamsShowModel extends ActionModelFactory
{   
    public $_back;
    public $_route;
    public $_person;
    public $_project;
    public $_template;
    
    public $person;
    public $personTeams;
    public $project;
    
    protected $personTeamRepo;
    
    public function __construct($personTeamRepo)
    {   
        $this->personTeamRepo = $personTeamRepo;
    }
    /* =====================================================
     * Process a posted model
     * Turn everything over to the workflow
     */
    public function process($formData)
    {   
        $role = isset($formData['role']) ? $formData['role'] : 'Parent';
        
        $person = $this->person;
        
        // Add teams
        $teamKeys = array();
        foreach($this->project->getPrograms() as $program)
        {
            $teamKeys = array_merge($teamKeys,$formData[$program . 'Teams']);
        }
        foreach($teamKeys as $teamKey)
        {
            // Skip if already have one
            if ($person->hasPersonTeam($teamKey)) continue;
            
            // Find it
            $event = new FindTeamEvent($teamKey);
            $this->dispatcher->dispatch(FindTeamEvent::ByKey,$event);
            $team = $event->getTeam();
            if ($team)
            {
                $personTeam = $person->createPersonTeam();
                $personTeam->setRole($role);
                $personTeam->setTeam($team);
                $person->addPersonTeam($personTeam);
            }
        }
        // Remove teams
        foreach($formData['personTeams'] as $personTeamx)
        {
            if ($personTeamx['remove'])
            {
                $person->removePersonTeam($personTeamx['personTeam']);
            }
        }
        $this->personTeamRepo->flush();
    }
    public function create(Request $request)
    {   
        $this->_back = $request->query->get('_back');
        
        $requestAttrs = $request->attributes;
        
        $this->_route    = $requestAttrs->get('_route');
        $this->_person   = $requestAttrs->get('_person');
        $this->_project  = $requestAttrs->get('_project');
        $this->_template = $requestAttrs->get('_template');
        
        $this->person  = $requestAttrs->get('userPerson');
        $this->project = $requestAttrs->get('project');
        
        $this->personTeams = $this->personTeamRepo->findAllByProjectPerson($this->project,$this->person);
      //die('Count ' . count($this->personTeams));
        return $this;
    }
}