<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\Person\Teams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

use Cerad\Bundle\CoreBundle\Event\FindProjectLevelsEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectTeamsEvent;

class TeamsShowFormFactory extends ActionFormFactory
{   
    public function create(Request $request, $model)
    {  
        // Try using a name just for grins
        $builder = $this->formFactory->createNamedBuilder('teams_show','form',$model);

        $actionUrl = $this->router->generate($model->_route,array
        (
            '_person'  => $model->_person,
            '_project' => $model->_project,
        ));
        $builder->setAction($actionUrl);

        $project  = $model->project;
        $programs = $project->getPrograms();
        foreach($programs as $program)
        {
            $findTeamsEvent = new FindProjectTeamsEvent($project,$program);
            $this->dispatcher->dispatch(FindProjectTeamsEvent::FindProjectTeams,$findTeamsEvent);
            $teams = $findTeamsEvent->getTeams();
            
            $teamChoices = array();
            foreach($teams as $team)
            {
                $desc = sprintf('%s %02d %s',$team->getLevelKey(),$team->getNum(),$team->getName());
                $teamChoices[$team->getId()] = $desc;
            }
            $builder->add($this->formFactory->createNamed($program . 'Teams', 'choice', null, array(
                'label'     => $program . ' Teams',
                'required'  => false,
                'choices'   => $model->teamChoices,
                'expanded'  => false,
                'multiple'  => false,
                'auto_initialize' => false,
            )));
        }
        
        $builder->add('commit','choice',array(
            'label' => 'Update',
            'choices' => array(0 => 'Test run - no updates', 1  => 'Update database')
        ));
                
        $builder->add('add', 'submit', array(
            'label' => 'Add Teams',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
