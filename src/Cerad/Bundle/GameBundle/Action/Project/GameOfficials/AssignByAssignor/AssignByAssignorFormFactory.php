<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class AssignByAssignorFormFactory extends ActionFormFactory
{   
    protected $personNameChoiceTpl;
    
    public function __construct($personNameChoiceTpl)
    {
        $this->personNameChoiceTpl = $personNameChoiceTpl;
    }
    public function create(Request $request, AssignByAssignorModel $model)
    {   
        // The 'form' is actually the type
        $builder = $this->formFactory->createBuilder('form',$model);

        $actionRoute = $request->attributes->get('_route');
        $actionUrl = $this->router->generate($actionRoute,array
        (
             'back'    => $model->back,
            '_game'    => $model->game->getNum(),
            '_project' => $request->attributes->get('_project'),
        ));
        $builder->setAction($actionUrl);
        
        $slotFormType = new AssignByAssignorSlotFormType(
                $model->workflow,
                $model->projectOfficials,
                $this->personNameChoiceTpl
        );
        $builder->add('gameOfficials','collection',array('type' => $slotFormType));
        
        $builder->add('assign', 'submit', array(
            'label' => 'Submit',
            'attr'  => array('class' => 'submit'),
        ));  
        $builder->add( 'reset','reset', array(
            'label' => 'Reset',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
