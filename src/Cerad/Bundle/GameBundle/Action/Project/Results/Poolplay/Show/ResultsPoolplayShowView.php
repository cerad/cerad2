<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results\Poolplay\Show;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResultsPoolplayShowView extends ActionController
{
    public function action(Request $request, ResultsPoolplayShowModel $model)
    {
        /*
        $form->handleRequest($request);
        if ($form->isValid()) 
        {   
            $model->process($request,$form->getData());
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
         }*/

        $_project = $request->attributes->get('_project');
        
        $routes = array();
        
        foreach(array('Core','Extra') as $program)
        {
            foreach(array('Boys','Girls') as $gender)
            {
                foreach(array('U10','U12','U14','U16','U19') as $age)
                {
                    $level = sprintf('AYSO_%s%s_%s',$age,substr($gender,0,1),$program);
                    $routes[$program][$gender][$age][$age] = $this->generateUrl(
                        'cerad_game__project__results_poolplay__show',
                        array('_project' => $_project, 'level' => $level,
                    ));
                    
                    // Need to apply proram as well
                    $pools = array('A','B','C','D');
                    if ($age == 'U10') $pools = array('A','B','C','D','E','F','G','H','I','J','K','L');
                    
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
        $pools = $model->loadPools();
       
        // And render
        $tplData = array();
        $tplData['pools']  = $pools;
        $tplData['routes'] = $routes;
        return $this->regularResponse($request->get('_template'),$tplData);
    }
}
