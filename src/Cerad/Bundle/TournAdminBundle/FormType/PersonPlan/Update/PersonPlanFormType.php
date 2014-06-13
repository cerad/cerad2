<?php
namespace Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\Email     as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

/* ===================================================================
 * Try having some standard admin forms
 * Might end up flattening everything and just moving to controller
 */
class PersonPlanFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_admin_person_plan_person_plan'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonPlan',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options;
        
        $builder->add('willAttend', 'cerad_person_plan_will_attend');
        $builder->add('willReferee','cerad_person_plan_will_referee');
        $builder->add('program',    'cerad_person_plan_program');
        
        $builder->add('wantMentor', 'cerad_person_plan_want_mentor');
        $builder->add('willMentor', 'cerad_person_plan_will_mentor');
        
        $builder->add('shirtSize',  'cerad_person_shirt_size');
        
        $builder->add('availSatAfternoon','cerad_person_plan_avail');
        $builder->add('availSunAfternoon','cerad_person_plan_avail');
        $builder->add('availSunMorning',  'cerad_person_plan_avail');
        
        $builder->add('notes', 'textarea' ,array(
            'required' => false,
            'attr' => array('rows' => 3, 'cols' => 40)
        ));
        
    }
}
?>
