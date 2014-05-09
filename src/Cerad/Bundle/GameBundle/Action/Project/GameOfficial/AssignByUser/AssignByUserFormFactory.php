<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficial\AssignByUser;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\Form\FormFactoryInterface;

class AssignByUserFormFactory
{
    protected $router;
    protected $formFactory;
    
    public function setRouter     (RouterInterface      $router)      { $this->router      = $router; }
    public function setFormFactory(FormFactoryInterface $formFactory) { $this->formFactory = $formFactory; }
    
    public function create(Request $request, AssignByUserModel $model)
    {   
        // The 'form' is actually the type
        $builder = $this->formFactory->createBuilder('form',$model);

        $actionRoute = $request->attributes->get('_route');
        $actionUrl = $this->router->generate($actionRoute,array
        (
            '_game'         => $model->game->getNum(),
            '_gameOfficial' => $model->gameOfficial->getSlot(),
            '_project'      => $request->attributes->get('_project'),
        ));
        $builder->setAction($actionUrl);
        
        $slotFormType = new AssignByUserSlotFormType(
                $model->workflow,
                $model->projectOfficial
        );
        $builder->add('gameOfficial',$slotFormType);
        
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
