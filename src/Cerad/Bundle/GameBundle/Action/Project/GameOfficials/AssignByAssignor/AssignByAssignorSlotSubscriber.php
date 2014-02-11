<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

class AssignByAssignorSlotSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $workflow;
    
    private $personGuidOptions;
    
    public function __construct(FormFactoryInterface $factory, $workflow, $personGuidOptions)
    {
        $this->factory  = $factory;
        $this->workflow = $workflow;
        
        $this->personGuidOptions = $personGuidOptions;
    }
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }
    public function preSetData(FormEvent $event)
    {
        $form         = $event->getForm();
        $gameOfficial = $event->getData();

        if (!$gameOfficial) return; // Called twice
        
        $states = $this->workflow->getStateOptionsForAssignorWorkflow($gameOfficial->getAssignState());
        
        $form->add($this->factory->createNamed('assignState','choice', null, array(
            'required'        => true,
            'auto_initialize' => false,
            'choices'         => $states,
        )));
        $form->add($this->factory->createNamed('personGuid','choice', null, array(
            'required'        => false,
            'auto_initialize' => false,
            'empty_value'     => 'Select Official',
            'empty_data'      => null,
            'choices'         => $this->personGuidOptions,
        )));
         
        return;
        
    }
}