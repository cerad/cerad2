<?php
namespace Cerad\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
//  Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints as Assert;

use Cerad\Bundle\UserBundle\Validator\Constraints\UsernameExists;

class UsernameExistsFormType extends AbstractType
{
    public function getName()   { return 'cerad_user_username_exists'; }
    public function getParent() { return 'text'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label'           => 'User Name',
            'attr'            => array('size' => 30),
            'constraints'     => array(
                new Assert\NotNull(array('message' => 'User Name is required')), 
                new UsernameExists(),
            )
        ));
    }
}

?>
