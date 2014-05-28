<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results\Poolplay\Show;

use Cerad\Bundle\CoreBundle\Action\ActionView;

use Symfony\Component\HttpFoundation\Request;

class ResultsPoolplayShowView extends ActionView
{
    protected $projectPools;
    
    public function __construct($projectPools)
    {
        $this->projectPools = $projectPools;
    }
    public function renderResponse(Request $request)
    {
        $model = $request->attributes->get('model');

        $_project = $request->attributes->get('_project');
        
        $projectPools = $this->projectPools;
        
        $routes = array();
        
        foreach($projectPools as $program => $genders)
        {
           
            foreach($genders as $gender => $ages)
            {
                foreach($ages as $age => $pools)
                {
                    $ageGender = $age . substr($gender,0,1);
                    
                    $level = sprintf('AYSO_%s_%s',$ageGender,$program);
                    $routes[$program][$gender][$age][$ageGender] = $this->generateUrl(
                        'cerad_game__project__results_poolplay__show',
                        array('_project' => $_project, 'level' => $level,
                    ));                    
                    foreach($pools as $pool)
                    {
                        $routes[$program][$gender][$age][$pool] = $this->generateUrl(
                            'cerad_game__project__results_poolplay__show',
                            array('_project' => $_project, 'level' => $level, 'pool' => $pool,
                         ));
                    }
                }
            }
        }
       
        // And render
        $tplData = array();
        $tplData['pools']  = $model->loadPools();
        $tplData['routes'] = $routes;
        return $this->regularResponse($request->get('_template'),$tplData);
    }
}
