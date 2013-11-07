<?php
namespace Cerad\Bundle\PersonBundle\FormType\AYSO;

use Symfony\Component\Form\AbstractType;
//  Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/* ==================================================================
 * Use this to collect and partially validate a region number
 * The transformer will yield AYSORxxxx
 */
class MemYearFormType extends AbstractType
{   
    public function getName()   { return 'cerad_person_ayso_mem_year'; }
    public function getParent() { return 'choice'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
          //'invalid_message' => 'Unknown Region Number',
            
            'label'    => 'AYSO Membership Year',
            'choices'  => $this->choices,
            'multiple' => false,
            'expanded' => false,
        ));
    }
    
    protected $choices = array
    (
        'None'   => 'None',
        'FS2013' => 'FS2013',
        'FS2014' => 'FS2014',
        'FS2015' => 'FS2015',
        
        'FS2012' => 'FS2012',
        'FS2011' => 'FS2011',
        'FS2010' => 'FS2010',
        'FS2009' => 'FS2009',
        'FS2008' => 'FS2008',

    );
}

?>
