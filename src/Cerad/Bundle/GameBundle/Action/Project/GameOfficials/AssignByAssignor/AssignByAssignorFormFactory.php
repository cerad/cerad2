<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\Form\FormFactoryInterface;

class AssignByAssignorFormFactory
{
    protected $router;
    protected $formFactory;
    
    public function setRouter     (RouterInterface      $router)      { $this->router      = $router; }
    public function setFormFactory(FormFactoryInterface $formFactory) { $this->formFactory = $formFactory; }
    
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
                $model->projectOfficials
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
