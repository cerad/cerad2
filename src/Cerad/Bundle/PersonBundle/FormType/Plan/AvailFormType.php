<?php
namespace Cerad\Bundle\PersonBundle\FormType\Plan;

use Symfony\Component\Form\AbstractType;
//  Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AvailFormType extends AbstractType
{   
    public function getParent() { return 'choice'; }
    public function getName()   { return 'cerad_person_plan_avail'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $label = 'Availability';
        
        $resolver->setDefaults(array(
          
            'label'    => $label,
            'choices'  => $this->choices,
            'multiple' => false,
            'expanded' => false,
            
            'empty_value' => $label,
            'empty_data'  => null
            
        ));
    }    
    protected $choices = array
    (
        'no'    => 'No', 
        'yes'   => 'Yes', 
        'maybe' => 'Yes - If My Team Advances',
    );
}
?>
