<?php
namespace Cerad\Bundle\PersonBundle\FormType\Plan;

use Symfony\Component\Form\AbstractType;
//  Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/* ==================================================================
 * Use this to collect and partially validate a region number
 * The transformer will yield AYSORxxxx
 */
class ProgramFormType extends AbstractType
{   
    public function getParent() { return 'choice'; }
    public function getName()   { return 'cerad_person_plan_program'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
          
            'label'    => 'Program',
            'choices'  => $this->choices,
            'multiple' => false,
            'expanded' => false,
            
            'empty_value' => 'Program?',
            'empty_data'  => null
            
        ));
    }    
    protected $choices = array
    (
        'na'    => 'Not yet answered',
        'core'  => 'Torrance/Redondo Beach (CORE site)',
        'extra' => 'Riverside (EXTRA site)',
        'other' => 'Where needed (either site)',
   );
}

?>
