<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssignByAssignorSlotFormType extends AbstractType
{
    public function getName() { return 'cerad_game__game_official__assign_by_assignor_slot'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Doctrine\Entity\GameOfficial',
        ));
    }
    protected $workflow;
    protected $officialGuidChoices;
    
    public function __construct($workflow,$officials,$personNameChoiceTpl)
    {
        $this->workflow = $workflow;
        
        $guids = array();
        foreach($officials as $official)
        {
            $desc = $personNameChoiceTpl->render($official);
            
            $guids[$official->getGuid()] = $desc;
        }
        $this->officialGuidChoices = $guids;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('role', 'text', array(
            'attr'      => array('size' => 10),
            'read_only' => true,
        ));
        $builder->add('personNameFull', 'text', array(
            'attr'      => array('size' => 30),
            'read_only' => true,
            'required'  => false,
        ));
        
        $subscriber = new AssignByAssignorSlotSubscriber(
            $builder->getFormFactory(),
            $this->workflow,
            $this->officialGuidChoices
        );

        $builder->addEventSubscriber($subscriber);
        
        if ($options);
    }
}

