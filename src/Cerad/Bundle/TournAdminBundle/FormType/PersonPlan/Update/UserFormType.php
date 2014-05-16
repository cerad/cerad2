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
class UserFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_admin_person_plan_user'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\UserBundle\Model\User',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {    
        // Need to deal with people with no user accounts
        $builder->add('accountName','text', array(
            'required' => false,
            'label'    => 'Account Name',
            'trim'     => true,
            'constraints' => array(
              //new NotBlankConstraint(),
            ),
            'attr' => array('size' => 30),
        )); 
        $builder->add('username','text', array(
            'required' => false,
            'label'    => 'Account User',
            'trim'     => true,
            'constraints' => array(
              //new NotBlankConstraint(),
            ),
            'attr' => array('size' => 30),
        )); 
        $builder->add('email','email', array(
            'required' => false,
            'label'    => 'Account Email',
            'trim'     => true,
            'constraints' => array(
              //new NotBlankConstraint(),
            ),
            'attr' => array('size' => 30),
        )); 
    }
}
?>
