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
    
    protected function genVenueNameChoices($rows)
    {
        $items = array();
        foreach($rows as $row)
        {
            $items[$row['venueName']] = $row['venueName'];
        }
        ksort($items);
        return $items;
    }
    protected function genFieldNameChoices($rows)
    {
        $items = array();
        foreach($rows as $row)
        {
            $items[$row['fieldName']] = sprintf('%s - %s',$row['fieldName'],$row['venueName']);
        }
        ksort($items);
        return $items;
    }
    public function create(Request $request, GameUpdateByScorerModel $model)
    {   
        // The 'form' is actually the type
        $builder = $this->formFactory->createBuilder('form',$model->game);

        $actionRoute = $model->_route;
        $actionUrl = $this->router->generate($actionRoute,array
        (
            '_project' => $model->_project,
               '_game' => $model->_game,
                'back' => $model->back,
        ));
        $builder->setAction($actionUrl);
        
        $builder->add('dtBeg','datetime',array(
            'label'   => 'Date Time',
            'years'   => array(2014), // Hack
            'months'  => array(7),
            'days'    => array(2,3,4,5,6),
            'minutes' => array(0,5,10,15,20,25,30,35,40,45,50,55),
        ));
        $venueFields = $model->findVenueFields();
        
        $builder->add('venueName', 'choice', array(
            'choices'  => $this->genVenueNameChoices($venueFields),
            'expanded' => false,
            'multiple' => false,
            'required' => true,
        ));
        $builder->add('fieldName', 'choice', array(
            'choices'  => $this->genFieldNameChoices($venueFields),
            'expanded' => false,
            'multiple' => false,
            'required' => true,
        ));
        $teams = $model->findPhysicalTeams();
        $gameTeamFormType = new GameUpdateByScorerTeamFormType($teams);
        
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
