<?php
namespace Cerad\Bundle\TournBundle\FormType\Schedule\My;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

class GameOfficialSubscriber implements EventSubscriberInterface
{
    private $person;
    private $factory;

    public function __construct(FormFactoryInterface $factory, $person)
    {
        $this->factory = $factory;
        $this->person  = $person;
    }

    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that we want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        $gameOfficial = $event->getData();
        $form         = $event->getForm();

        if (!$gameOfficial) return;
        
        /* ==============================================================
         * This worflow logic needs to get moved to some sort of service
         */
        $states = $this->getStates($this->person,$gameOfficial);
        if (!$states)
        {
            $form->add($this->factory->createNamed('state', 'text', null, array(
                'label'           => 'State',
                'required'        => false,
                'auto_initialize' => false,
                'read_only'       => true,
            )));
            return;            
        }
        // Select
        $form->add($this->factory->createNamed('state','choice', null, array(
            'required'        => true,
          //'empty_value'     => 'Assignment State',
          //'empty_data'      => null,
            'auto_initialize' => false,
            'choices'         => $states,
        )));
         
        // Done
        return;
    }
    /* ===============================================
     * Processing the logged in user
     * Stronly implies we have a state
     */
    protected function getStates($person,$gameOfficial)
    {
        // See if person is on the game
        // Probably shoud use guid here
        if ($person->getName()->full != $gameOfficial->getPersonNameFull()) return null;
        
        // Workflow based on current state
        $state = $gameOfficial->getState();
        
        // Should not happen
        if (!$state) return null;
        
        // This should already have been done
        if ($state == 'Published')
        {
            $state = 'Notified';
            $gameOfficial->setState($state);
        }
        // Official can accept or decline new assignment
        if ($state == 'Notified')
        {
            return array
            (
                'Notified'  => 'Notified',  // Official has been notified
                'Accepted'  => 'Accepted',  // Official has accepted
                'Declined'  => 'Declined',  // Official declined
            );
        }
        // Official can turn back existing assignment
        if ($state == 'Accepted')
        {
            return array
            (
                'Accepted'  => 'Accepted',  // Official has accepted
                'Turnback'  => 'Turnback',  // Official declined
            );
        }
        
        // Anything else, no changes
        // Pending, Declined, Turnback
        return null;
    }
}
?>
