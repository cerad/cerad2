<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Team\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class ScheduleTeamShowFormFactory extends ActionFormFactory
{
    public function create(Request $request, $model)
    {   
        $actionUrl = $this->generateUrl(
            'cerad_game__project__schedule_team__show',
            array('_project' => $request->attributes->get('_project'))
        );
        $formOptions = array(
            'method' => 'POST',
            'action' => $actionUrl,
            'attr'   => array(
                'class' => 'cerad_common_form1',
            ),
            'required' => false,
        );

        $builder = $this->formFactory->create('form',$model->criteria,$formOptions);
        
        $programs = $model->project->getPrograms();
        foreach($programs as $program)
        {
        $builder->add($this->formFactory->createNamed($program . 'Teams', 'choice', null, array(
                'label'     => $program . ' Teams',
                'required'  => false,
                'choices'   => $model->loadTeamChoices($program),
                'expanded'  => false,
                'multiple'  => true,
                'auto_initialize' => false,
        )));
        }
        $builder->add('search', 'submit', array(
            'label' => 'Search',
            'attr' => array('class' => 'submit'),
        ));        
       
        return $builder;        
    }
}