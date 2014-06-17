<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameReport\Update;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class GameReportUpdateFormFactory extends ActionFormFactory
{
    public function create(Request $request, $model)
    {   
        $actionUrl = $this->generateUrl(
            'cerad_game__project__game_report__update',
            array(
                '_project' => $request->attributes->get('_project'),
                '_game'    => $request->attributes->get('_game'),
                'back'     => $request->query->get('back'),
            )
        );
        $formOptions = array(
            'method' => 'POST',
            'action' => $actionUrl,
            'attr'   => array(
                'class' => 'cerad_common_form1',
            ),
            'required' => false,
        );
        $nextGameNum = $model->game ? $model->_game + 1 : $model->_game;
        
        $formData = array(
            'game'           => $model->game,
            'gameReport'     => $model->gameReport,
            'homeTeamReport' => $model->homeTeamReport,
            'awayTeamReport' => $model->awayTeamReport,
            'nextGameNum'    => $nextGameNum,
        );
        $builder = $this->formFactory->create('form',$formData,$formOptions);
        
        $builder->add('game',           new FormType\GameFormType());
        $builder->add('gameReport',     new FormType\GameReportFormType());
        $builder->add('homeTeamReport', new FormType\GameTeamReportFormType());
        $builder->add('awayTeamReport', new FormType\GameTeamReportFormType());
        
        $builder->add('nextGameNum','integer');
 
        $builder->add('save', 'submit', array(
            'label' => 'Save',
            'attr' => array('class' => 'submit'),
        ));        
        $builder->add('next', 'submit', array(
            'label' => 'Save Then Next',
            'attr' => array('class' => 'submit'),
        ));        
        return $builder;        
    }
}