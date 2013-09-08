<?php
namespace Cerad\Bundle\UserBundle\FormType;

use Symfony\Component\Form\AbstractType;
//  Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints as Assert;

use Cerad\Bundle\UserBundle\ValidatorConstraint\EmailExistsConstraint;

class EmailExistsFormType extends AbstractType
{
    public function getName()   { return 'cerad_user_email_exists'; }
    public function getParent() { return 'text'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label'           => 'Email',
            'attr'            => array('size' => 30),
            'constraints'     => array(
                new Assert\NotNull(array('message' => 'Email is required')), 
                new Assert\Email  (array('message' => 'Email is invalid' )), 
                new EmailExistsConstraint(),
            )
        ));
    }
}

?>
