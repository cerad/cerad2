<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class TeamsShowModel extends ActionModelFactory
{   
    public $project;
    
    public function __construct()
    {
    }
    /* =====================================================
     * Process a posted model
     * Turn everything over to the workflow
     */
    public function process()
    {   
    }
    public function create(Request $request)
    {   
        $criteria = array(
            'programs' => array(),
            'genders'  => array(),
            'ages'     => array(),
        );
        foreach($request->query->all() as $key => $value)
        {
            $criteria[$key] = explode(',',$value); // xxx= gives empty array
        }
        print_r($criteria); die();
        
        $requestAttrs = $request->attributes;
        
        $this->project = $project = $requestAttrs->get('project');
                
        $criteria['projectKey'] = $project->getKey();
        
        $this->criteria = $criteria;
        
        return $this;
    }
}