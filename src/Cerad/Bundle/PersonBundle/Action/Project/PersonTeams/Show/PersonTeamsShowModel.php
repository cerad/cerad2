<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonTeams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class PersonTeamsShowModel extends ActionModelFactory
{   
    public $_back;
    public $_route;
    public $_person;
    public $_project;
    public $_template;
    
    public $person;
    public $project;
    
    public $formData;
    
    protected $personRepo;
    
    public function __construct($personRepo)
    {   
        $this->personRepo = $personRepo;
    }
    /* =====================================================
     * Process a posted model
     * Turn everything over to the workflow
     */
    public function process()
    {   
        $file = $this->attachment;
        
      //echo sprintf("Max file size %d %d Valid: %d, Error: %d<br />\n",
      //    $file->getMaxFilesize(),$file->getClientSize(),$file->isValid(), $file->getError());
        
        $teams = $this->reader->read($this->project,$file->getPathname());

        $saveResults = $this->saver->save($teams,$this->commit);
        $saveResults->basename = $file->getClientOriginalName();
        
        $linkResults = $this->linker->link($teams,$this->commit);
        $linkResults->basename = $file->getClientOriginalName();
        
        // TODO: Some sort of merge or maybe return an array?
        
        return $linkResults;
    }
    public function create(Request $request)
    {   
        $this->_back = $request->query->get('_back');
        
        $requestAttrs = $request->attributes;
        
        $this->_route    = $requestAttrs->get('_route');
        $this->_person   = $requestAttrs->get('_person');
        $this->_project  = $requestAttrs->get('_project');
        $this->_template = $requestAttrs->get('_template');
        
        $this->person  = $requestAttrs->get('person');
        $this->project = $requestAttrs->get('project');
        
        $formData = array();
        
        // Divide teams by programs
        $programs = $this->project->getPrograms();
        foreach($programs as $program)
        {
            $formData[$program . 'Teams' ] = array();
        }
        // Add existing teams
        
        // Store
        $this->formData = $formData;
        
        return $this;
    }
}