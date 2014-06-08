<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\Person\Teams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class TeamsShowFormFactory extends ActionFormFactory
{   
    public function create(Request $request, $model)
    {   
        die('form');
        // Try using a name just for grins
        $builder = $this->formFactory->createNamedBuilder('teams_import','form',$model);

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
                
        $builder->add('import', 'submit', array(
            'label' => 'Import Teams',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
