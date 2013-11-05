<?php
namespace Cerad\Bundle\PersonBundle\FormType;

use Symfony\Component\Form\AbstractType;
//  Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/* ==================================================================
 * Use this to collect and partially validate a region number
 * The transformer will yield AYSORxxxx
 */
class DobFormType extends AbstractType
{   
    public function getParent() { return 'birthday'; }
    public function getName()   { return 'cerad_person_dob'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
          
            'label'    => 'DOB (mm/dd/yyyy)',
            'required' => false,
            'input'    => 'datetime',
            'widget'   => 'single_text',
            'format'   => 'M/d/yyyy',
        ));
    }
}

?>
