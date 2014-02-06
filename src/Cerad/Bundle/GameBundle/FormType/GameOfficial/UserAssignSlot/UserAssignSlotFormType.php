<?php
namespace Cerad\Bundle\GameBundle\FormType\GameOfficial\UserAssignSlot;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserAssignSlotFormType extends AbstractType
{
    public function getName() { return 'cerad_game__game_official__user_assign_slot'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Entity\GameOfficial',
        ));
    }
    protected $officials;
    protected $assignSlotWorkflow;
    
    public function __construct($assignSlotWorkflow)
    {
        $this->assignSlotWorkflow = $assignSlotWorkflow;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('role', 'text', array(
            'attr'      => array('size' => 10),
            'read_only' => true,
        ));
        $builder->add('personNameFull', 'text', array(
            'attr'      => array('size' => 40),
            'read_only' => true,
        ));
        
        $subscriber = new UserAssignSlotSubscriber($builder->getFormFactory(),$this->assignSlotWorkflow);
        $builder->addEventSubscriber($subscriber);
    }
}
?>
