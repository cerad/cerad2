<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonPersons\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\CoreBundle\Event\Team\FindTeamEvent;

class PersonPersonsShowModel extends ActionModelFactory
{   
    public $_back;
    public $_route;
    public $_person;
    public $_project;
    public $_template;
    
    public $person;
    public $personPersons;
    public $project;
    
    protected $personRepo;
    protected $personPersonRepo;
    
    public function __construct($personRepo,$personPersonRepo)
    {   
        $this->personRepo       = $personRepo;
        $this->personPersonRepo = $personPersonRepo;
    }
    public function process($formData)
    {   
        $person = $this->person;
        
        $role = isset($formData['role']) ? $formData['role'] : 'Family';
        $name = $formData['name'];
        if ($name)
        {
            // Search
            $personAdd = $this->personRepo->findOneByProjectName($this->project->getKey(),$name);
            if ($personAdd)
            {
                // Add
                $personPerson = $person->createPersonPerson();
                $personPerson->setRole  ($role);
                $personPerson->setChild ($personAdd);
                $personPerson->setParent($person);
                $person->addPersonPerson($personPerson);
            }
        }
        
        // Remove preple
        foreach($formData['personPersons'] as $personPersonx)
        {
            if ($personPersonx['remove'])
            {
                $personPerson = $personPersonx['personPerson'];
                if (!$personPerson->isRolePrimary())
                {
                    $person->removePersonPerson($personPerson);
                }
            }
        }
        $this->personPersonRepo->flush();
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
        
        $this->personPersons = $this->person->getPersonPersons();
        
        return $this;
    }
}