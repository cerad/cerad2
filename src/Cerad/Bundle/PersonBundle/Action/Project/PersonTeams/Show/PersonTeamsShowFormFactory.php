<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonTeams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

//  Cerad\Bundle\CoreBundle\Event\FindProjectLevelsEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectTeamsEvent;

class PersonTeamsShowFormFactory extends ActionFormFactory
{   
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
        // Try using a name just for grins
        $builder = $this->formFactory->create('form',$model->formData,$formOptions);
        
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
                        
        $builder->add('add', 'submit', array(
            'label' => 'Add Team(s)',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder; //->getForm();
    }
}
