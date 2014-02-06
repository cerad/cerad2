<?php
/* =================================================
 * 21 June 2013
 * This is not currently being used for the tournament search form
 * 
 * 23 Sep 2013
 * It will be now
 */
namespace Cerad\Bundle\TournBundle\FormType\Schedule\Official;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{
    private $factory;
    
    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(
            FormEvents::PRE_SET_DATA => array('onPreSetData'),
            FormEvents::POST_SUBMIT  => array('onPostSubmit',900),
        );
    }
    public function onPostSubmit(FormEvent $event)
    {
      //$event->stopPropagation();
    }
    public function onPreSetData(FormEvent $event)
    {
        $criteria = $event->getData();

        if ($criteria === null) return;
        
        $form = $event->getForm();
        $factory = $this->factory;
        
        foreach($criteria['searches'] as $key => $search)
        {
            $choices = $search['choices'];
            
            $form->add($factory->createNamed($key, 'choice', null, array(
                'label'     => $search['label'],
                'required'  => true,
                'choices'   => $choices, // Fri = label, 2013-06-14 = value
                'expanded'  => true,
                'multiple'  => true,
                'auto_initialize' => false,
            )));     
        }
        return;
        
        // Generate field pick list
        $fields = $this->manager->loadFieldChoices($data);
        array_unshift($fields,'All Fields');
        $form->add($this->factory->createNamed('fields', 'choice', null, array(
            'label'         => 'Fields',
            'required'      => false,
            'choices'       => $fields,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        // Generate team pick list
        $teams = $this->manager->loadTeamChoices($data);
        array_unshift($teams,'All Teams');
        $form->add($this->factory->createNamed('teams', 'choice', null, array(
            'label'         => 'Teams',
            'required'      => false,
            'choices'       => $teams,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        // Generate levels pick list
        $levels  = $this->manager->loadLevelChoices($data);
        array_unshift($levels,'All Levels');
        $form->add($this->factory->createNamed('levels', 'choice', null, array(
            'label'         => 'Levels',
            'required'      => false,
            'choices'       => $levels,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        // Generate sports pick list
        $names  = $this->manager->loadDomainSubChoices($data);
        array_unshift($names,'All Sub Groups');
        $form->add($this->factory->createNamed('domainSubs', 'choice', null, array(
            'label'         => 'Sub Groups',
            'required'      => false,
            'choices'       => $names,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        // Generate groups pick list
        $names  = $this->manager->loadDomainChoices($data);
        array_unshift($names,'All Groups');
        $form->add($this->factory->createNamed('domains', 'choice', null, array(
            'label'         => 'Groups',
            'required'      => false,
            'choices'       => $names,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        
        // Generate seasons pick list
        $names = $this->manager->loadSeasonChoices($data);
        array_unshift($names,'All Seasons');
        $form->add($this->factory->createNamed('seasons', 'choice', null, array(
            'label'         => 'Seasons',
            'required'      => false,
            'choices'       => $names,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 4),
        )));
        // Generate sports pick list
        $names = $this->manager->loadSportChoices($data);
        array_unshift($names,'All Sports');
        $form->add($this->factory->createNamed('sports', 'choice', null, array(
            'label'         => 'Sports',
            'required'      => false,
            'choices'       => $names,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 4),
        )));
    }
}
?>
