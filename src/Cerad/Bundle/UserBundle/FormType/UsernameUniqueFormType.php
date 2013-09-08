<?php
namespace Cerad\Bundle\UserBundle\FormType;

use Symfony\Component\Form\AbstractType;
/// Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints as Assert;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameUniqueConstraint;

class UsernameUniqueFormType extends AbstractType
{
    public function getName()   { return 'cerad_user_username_unique'; }
    public function getParent() { return 'text'; }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label'           => 'User Name',
            'attr'            => array('size' => 30),
            'constraints'     => array(
                new Assert\NotNull(array('message' => 'User Name is required')), 
                new UsernameUniqueConstraint(),
            )
        ));
    }
}

?>
