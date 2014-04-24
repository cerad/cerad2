<?php

namespace Cerad\Bundle\TournBundle\Action\RefereeSchedule\Show;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameOrEmailExistsConstraint;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class RefereeScheduleShowFormFactory extends ActionFormFactory
{
    public function create(Request $request, $model)
    {   
        $formOptions = array(
            'method' => 'POST',
            'action' => $this->generateUrl('cerad_tourn__referee_schedule__show'),
            'attr'   => array(
                'class' => 'cerad_common_form1',
            ),
            'required' => false,
        );
        $constraintOptions = array();
        
        $builder = $this->formFactory->create('form',$model->formData,$formOptions);

        $builder->add('Fri','checkbox');
        $builder->add('Sat','checkbox');
        $builder->add('Sun','checkbox');
        
        $builder->add('U10B','checkbox');
        $builder->add('U10G','checkbox');
        $builder->add('U12B','checkbox');
        $builder->add('U12G','checkbox');
        $builder->add('U14G','checkbox');
        $builder->add('U19G','checkbox');
        
        $builder->add('search', 'submit', array(
            'label' => 'Search',
            'attr' => array('class' => 'submit'),
        ));        
       
        return $builder;
        
        $builder->add('username','text', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new UsernameOrEmailExistsConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
         $builder->add('password','password', array(
            'required' => true,
            'label'    => 'Password',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('rememberMe','checkbox',array(
            'required' => false,
            'label'    => 'Remember Me',
        ));
        
        $builder->add('login', 'submit', array(
            'label' => 'Log In',
            'attr'  => array('class' => 'submit'),
        ));
        
        // Actually a form
        return $builder;
    }
}