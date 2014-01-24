<?php
namespace Cerad\Bundle\TournBundle\FormType\Schedule\Official;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SelfAssignSlotFormType extends AbstractType
{
    public function getName() { return 'schedule_official_selfassign_slot'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Entity\GameOfficial',
        ));
    }    
    public function __construct($officials = null)
    {
        $this->officials = $officials;
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
        
        // Needs to be dynamic
        $states = array
        (
            'Turnback'  => 'Request Removal From Assignment',  // Official accepted but then bailed
            
            'Requested' => 'Requested', // Official has requested assignment
            
            'Approved'  => 'Approved',     // Assignor has approved request
            'Reviewing' => 'Under Review', // Assignor is reviewing request
          //'Rejected'  => 'Rejected',  // Assignor has rejected request
        );
        $builder->add('state','choice', array(
            'required'        => true,
            'empty_value'     => 'Assignment Status',
            'empty_data'      => null,
            'auto_initialize' => false,
            'choices'         => $states,
        ));
        
        // $subscriber = new SelfAssignSlotSubscriber($builder->getFormFactory(),$this->officials);
        // $builder->addEventSubscriber($subscriber);
    }
}
?>
