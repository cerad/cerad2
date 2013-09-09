<?php
namespace Cerad\Bundle\PersonBundle\FormType\AYSO;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Cerad\Bundle\PersonBundle\DataTransformer    \AYSO\VolunteerIdTransformer as IdTransformer;
use Cerad\Bundle\PersonBundle\ValidatorConstraint\AYSO\VolunteerIdConstraint  as IdConstraint;

class VolunteerIdFormType extends AbstractType
{
    protected $fake;
    
    public function getName()   { return 'cerad_person_ayso_volunteer_id'; }
    public function getParent() { return 'text'; }

    public function __construct($fake = false)
    {
        $this->fake = $fake;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Do the double transformer to handle errors
        $transformer = new IdTransformer($this->fake);
        $builder->addModelTransformer($transformer);
        $builder->addViewTransformer ($transformer);
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'AYSO Volunteer ID (8-digits)',
            'attr'  => array('size' => 10),
          //'constraints' => new IdConstraint(),
        ));
    }
}

?>
