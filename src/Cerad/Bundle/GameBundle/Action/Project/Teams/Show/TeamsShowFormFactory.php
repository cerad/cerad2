<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class TeamsShowFormFactory extends ActionFormFactory
{   
    protected function addCheckBoxes($builder,$name,$params)
    {
        
    }
    public function create(Request $request, $model)
    {   
        // Try using a name just for grins
        $projectSearched  = $model->project->getSearches();
        
        $formData = $model->criteria;
        
        $builder = $this->formFactory->createNamedBuilder('projectTeamsShow','form',$model->criteria);

        $actionRoute = $request->attributes->get('_route');
        $actionUrl = $this->router->generate($actionRoute,array
        (
            '_project' => $request->attributes->get('_project'),
        ));
        $builder->setAction($actionUrl);
        
        $builder->add('commit','choice',array(
            'label' => 'Update',
            'choices' => array(0 => 'Test run - no updates', 1  => 'Update database')
        ));
        $builder->add('programs','choice',array(
            'label' => 'Operation',
            'choices' => array(
                0 => 'Select Operation',
                1 => 'Update team names and soccerfest points', 
                2 => 'Update team slots')
        ));
                
        $builder->add('import', 'submit', array(
            'label' => 'Import Teams',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
