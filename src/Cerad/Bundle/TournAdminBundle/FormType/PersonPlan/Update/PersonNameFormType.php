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
class PersonNameFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_admin_person_plan_person_name'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonName',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('full','text', array(
            'required' => true,
            'label'    => 'Full Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint(),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('first','text', array(
            'required' => true,
            'label'    => 'First Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint(),
            ),
            'attr' => array('size' => 20),
        ));
       $builder->add('last','text', array(
            'required' => true,
            'label'    => 'Last Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint(),
            ),
            'attr' => array('size' => 20),
        ));
    }
}
?>
