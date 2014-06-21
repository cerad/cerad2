<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonPersons\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

//  Cerad\Bundle\CoreBundle\Event\FindProjectLevelsEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectTeamsEvent;

use Cerad\Bundle\PersonBundle\Action\Project\PersonPersons\Show\PersonPersonsShowPersonPersonFormType 
 as PersonPersonFormType;

class PersonPersonsShowFormFactory extends ActionFormFactory
{   
    protected function genFormData($model)
    {
        $formData = array(
            'role'          => 'Family',
            'personPersons' => array(),
        );
        
        // Divide teams by programs
        $programs = $model->project->getPrograms();
        foreach($programs as $program)
        {
            $formData[$program . 'Teams' ] = array();
        }
        // Wrap teams with delete options
        foreach($model->personTeams as $personTeam)
        {
            $formData['personTeams'][] = array('personTeam' => $personTeam, 'remove' => false);
        }
        // Done
        return $formData;
    }
    public function create(Request $request, $model)
    {  
        $actionUrl = $this->router->generate($model->_route,array
        (
            '_person'  => $model->_person,
            '_project' => $model->_project,
            '_back'    => $model->_back,
        ));
        $formOptions = array(
            'method' => 'POST',
            'action' => $actionUrl,
            'attr'   => array(
                'class' => 'cerad_common_form1',
            ),
            'required' => false,
        );
        $formData = $this->genFormData($model);
        
        // Try using a name just for grins
        $builder = $this->formFactory->create('form',$formData,$formOptions);
        
        $builder->add('personPersons','collection',array('type' => new PersonPersonFormType()));
        
        foreach($model->project->getPrograms() as $program)
        {
            $event = new FindProjectTeamsEvent($model->project,$program);
            $this->dispatcher->dispatch(FindProjectTeamsEvent::Find,$event);
            $teamChoices = array(0 => 'Select Team(s)');
            foreach($event->getTeams() as $team)
            {
                $teamChoices[$team->getKey()] = $team->getDesc();
            }
            $builder->add($this->formFactory->createNamed($program . 'Teams', 'choice', null, array(
                'label'     => $program . ' Teams',
                'required'  => false,
                'choices'   => $teamChoices,
                'expanded'  => false,
                'multiple'  => true,
                'auto_initialize' => false,
                
              // No impact with multiple = true
              //'empty_data'  => null,
              //'empty_value' => 'Choose Team(s)',
            )));
        }
        $builder->add('role','cerad_person__person_team__role');
        
        $builder->add('add', 'submit', array(
            'label' => 'Add Team(s)',
            'attr'  => array('class' => 'submit'),
        ));  
        $builder->add('remove', 'submit', array(
            'label' => 'Remove Selected Team(s)',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder; //->getForm();
        
        if ($request);
    }
}
