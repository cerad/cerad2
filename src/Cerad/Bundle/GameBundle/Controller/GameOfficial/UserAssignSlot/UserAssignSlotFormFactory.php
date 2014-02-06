<?php
namespace Cerad\Bundle\GameBundle\Controller\GameOfficial\UserAssignSlot;

use Symfony\Component\HttpFoundation\ParameterBag;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\Form\FormFactoryInterface;

// Cerad\Bundle\GameBundle\FormType\GameOfficial\UserAssignSlotFormType;

class UserAssignSlotFormFactory
{
    protected $router;
    protected $formFactory;
    protected $userAssignSlotFormType;
    
    // Inject form type just make the dependency clear
    // Could just new it
    public function __construct($userAssignSlotFormType)
    {
        $this->userAssignSlotFormType = $userAssignSlotFormType;
    }
    public function setRouter     (RouterInterface      $router)      { $this->router      = $router; }
    public function setFormFactory(FormFactoryInterface $formFactory) { $this->formFactory = $formFactory; }
    
    public function create(ParameterBag $requestAttributes, UserAssignSlotModel $model)
    {
        $game = $model->game;
        $slot = $model->slot;
        
        // The 'form' is actually the type
        $builder = $this->formFactory->createBuilder('form',$model);

        $actionRoute = $requestAttributes->get('_route');
        $actionUrl = $this->router->generate($actionRoute,
            array('game' => $game->getNum(),'slot' => $slot));
        
        $builder->setAction($actionUrl);
        
        $builder->add('gameOfficial',$this->userAssignSlotFormType);
        
        $builder->add('assign', 'submit', array(
            'label' => 'Submit',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
