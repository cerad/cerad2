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
class PersonFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_admin_person_plan_person'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\Person',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',new PersonNameFormType());
        
        $builder->add('email','email', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint(),
                new EmailConstraint   (),
            ),
            'attr' => array('size' => 30),
        )); 
        $builder->add('phone','cerad_person_phone', array(
            'required' => false,
            'label'    => 'Cell Phone',
            'trim'     => true,
            'constraints' => array(
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('gender','cerad_person_gender', array(
            'required' => false,
        ));
        $builder->add('dob','cerad_person_date', array(
            'required' => false,
        ));
        $builder->add('notes','text', array(
            'required' => false,
            'label'    => 'Notes',
            'trim'     => true,
            'attr' => array('size' => 50),
        ));
    }
}
?>
