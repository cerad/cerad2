<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficial\AssignByUser;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

class AssignByUserSlotSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $workflow;
    
    public function __construct(FormFactoryInterface $factory, $workflow, $projectOfficial)
    {
        $this->factory  = $factory;
        $this->workflow = $workflow;
        
        $this->projectOfficial = $projectOfficial;
        
    }
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }
    public function preSetData(FormEvent $event)
    {
        $form         = $event->getForm();
        $gameOfficial = $event->getData();

        if (!$gameOfficial) return;
        
        $states = $this->workflow->getStateOptions($gameOfficial->getAssignState());
        
        $form->add($this->factory->createNamed('assignState','choice', null, array(
            'required'        => true,
            'auto_initialize' => false,
            'choices'         => $states,
        )));
        
        // Fill in user name if empty
        if (!$gameOfficial->getPersonNameFull())
        {
            $gameOfficial->setPersonNameFull($this->projectOfficial->getPersonName());
        }
        $form->add($this->factory->createNamed('personNameFull', 'text', null, array(
            'attr'      => array('size' => 30),
            'required'  => false,
            'read_only' => true,
            'auto_initialize' => false,
        )));
        return;
        
    }
}