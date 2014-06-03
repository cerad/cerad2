<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByImport;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class AssignByImportFormFactory extends ActionFormFactory
{   
    public function create(Request $request, AssignByImportModel $model)
    {   
        // Try using a name just for grins
        $builder = $this->formFactory->createNamedBuilder('assignByImport','form',$model);

        $actionRoute = $request->attributes->get('_route');
        $actionUrl = $this->router->generate($actionRoute,array
        (
            '_project' => $request->attributes->get('_project'),
        ));
        $builder->setAction($actionUrl);
        
        $builder->add('attachment', 'file');
        
        $builder->add('commit','choice',array(
            'label' => 'Update',
            'choices' => array(0 => 'Test run - no updates', 1  => 'Update database')
        ));
        
        $builder->add('state','choice',array(
            'label' => 'Assignment State',
            'choices' => array(
                'Pending'   => 'Pending', 
                'Published' => 'Published', 
                'Notified'  => 'Notified', 
                'Accepted'  => 'Accepted')
        ));
        
        $builder->add('verify','choice',array(
            'label' => 'Referees',
            'choices' => array(1 => 'Must Be Registered', 0 => 'Registration not required')
        ));
        
        $builder->add('import', 'submit', array(
            'label' => 'Import',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
