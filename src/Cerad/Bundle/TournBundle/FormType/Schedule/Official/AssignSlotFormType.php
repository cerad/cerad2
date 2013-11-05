<?php
namespace Cerad\Bundle\TournBundle\FormType\Schedule\Official;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssignSlotFormType extends AbstractType
{
    public function getName() { return 'schedule_official_assign_slot'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Entity\GameOfficial',
        ));
    }    
    public function __construct($officials)
    {
        $this->officials = $officials;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('role', 'text', array(
            'attr'      => array('size' => 10),
            'read_only' => true,
        ));
        $subscriber = new AssignSlotSubscriber($builder->getFormFactory(),$this->officials);
        $builder->addEventSubscriber($subscriber);
    }
}
?>
