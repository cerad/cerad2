<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Game\Update\ByScorer;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\Form\FormFactoryInterface;

class GameUpdateByScorerFormFactory
{
    protected $router;
    protected $formFactory;
    
    public function setRouter     (RouterInterface      $router)      { $this->router      = $router; }
    public function setFormFactory(FormFactoryInterface $formFactory) { $this->formFactory = $formFactory; }
    
    public function create(Request $request, GameUpdateByScorerModel $model)
    {   
        // The 'form' is actually the type
        $builder = $this->formFactory->createBuilder('form',$model->game);

        $actionRoute = $request->attributes->get('_route');
        $actionUrl = $this->router->generate($actionRoute,array
        (
            '_project' => $request->attributes->get('_project'),
               '_game' => $request->attributes->get('_game'),
                'back' => $request->query->get('back'),
        ));
        $builder->setAction($actionUrl);
        
        $builder->add('dtBeg','datetime',array(
            'label' => 'Date Time',
            'minutes' => array(0,5,10,15,20,25,30,35,40,45,50,55),
        ));
        /*
        $builder->add('field', 'entity', array(
            'class'    => 'Cerad\Bundle\GameBundle\Entity\GameField',
            'property' => 'name',
            'choices'  => $model->getGameFields(),
        ));*/
        $teamNameChoices = $model->getTeamNameChoices();
        $gameTeamFormType = new GameUpdateByScorerTeamFormType($teamNameChoices);
        
        $builder->add('teams','collection',array('type' => $gameTeamFormType));
    
        $builder->add('update', 'submit', array(
            'label' => 'Update',
            'attr'  => array('class' => 'submit'),
        ));  
        $builder->add( 'reset','reset', array(
            'label' => 'Reset',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
