<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\Persons\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class PersonsShowFormFactory extends ActionFormFactory
{   
    protected $roles;
    
    public function __construct($roles)
    {
        $this->roles = $roles;
    }
    protected function genFormData($model)
    {
        return $model->criteria;
    }
    public function create(Request $request, $model)
    {  
        $actionUrl = $this->router->generate($model->_route,array
        (
            '_project' => $model->_project,
        ));
        $formOptions = array(
            'method'   => 'POST',
            'action'   => $actionUrl,
            'required' => false,
            'attr'     => array('class' => 'cerad_common_form1'),
        );
        $formData = $this->genFormData($model);
        
        $builder = $this->formFactory->createNamed('projectPersonsSearch','form',$formData,$formOptions);
        
        $roleChoices = array('ROLE_NONE' => 'Specific Role');
        foreach(array_keys($this->roles) as $role)
        {
            $roleChoices[$role] = $role;
        }
        $builder->add('roles','choice',array(
            'label'   => 'Roles',
            'choices' => $roleChoices,
        ));
        $builder->add('search', 'submit', array(
            'label' => 'Search',
            'attr'  => array('class' => 'submit'),
        ));  
        $builder->add('reset', 'reset', array(
            'label' => 'Reset',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder; //->getForm();
        
        if ($request);
    }
}
