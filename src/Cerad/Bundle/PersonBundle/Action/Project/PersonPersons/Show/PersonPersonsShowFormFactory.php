<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonPersons\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

//  Cerad\Bundle\CoreBundle\Event\FindProjectLevelsEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectTeamsEvent;

use Cerad\Bundle\PersonBundle\Action\Project\PersonPersons\Show\PersonPersonsShowPersonPersonFormType 
 as PersonPersonFormType;

class PersonPersonsShowFormFactory extends ActionFormFactory
{   
    protected function genFormData($model)
    {
        $formData = array(
            'name'          =>  null,
            'role'          => 'Family',
            'personPersons' =>  array(),
        );
        
        // Remove options
        foreach($model->personPersons as $personPerson)
        {
            $formData['personPersons'][] = array('personPerson' => $personPerson, 'remove' => false);
        }
        // Done
        return $formData;
    }
    public function create(Request $request, $model)
    {  
        $actionUrl = $this->router->generate($model->_route,array
        (
            '_person'  => $model->_person,
            '_project' => $model->_project,
            '_back'    => $model->_back,
        ));
        $formOptions = array(
            'method'   => 'POST',
            'action'   => $actionUrl,
            'required' => false,
            'attr'     => array('class' => 'cerad_common_form1'),
        );
        $formData = $this->genFormData($model);
        
        $builder = $this->formFactory->create('form',$formData,$formOptions);
        
        $builder->add('personPersons','collection',array('type' => new PersonPersonFormType()));
        
        $builder->add('role','cerad_person__person_person__role');
        $builder->add('name',  'text');
        $builder->add('fedNum','text',array('attr' => array('size' => 10)));
        
        $builder->add('add', 'submit', array(
            'label' => 'Add Person',
            'attr'  => array('class' => 'submit'),
        ));  
        $builder->add('remove', 'submit', array(
            'label' => 'Remove Selected Person(s)',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder; //->getForm();
        
        if ($request);
    }
}
