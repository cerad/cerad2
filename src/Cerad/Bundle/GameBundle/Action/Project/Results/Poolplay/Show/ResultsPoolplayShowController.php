<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results\Poolplay\Show;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResultsPoolplayShowController extends ActionController
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
                    $ageGender = $age . substr($gender,0,1);
                    
                    $level = sprintf('AYSO_%s_%s',$ageGender,$program);
                    $routes[$program][$gender][$age][$ageGender] = $this->generateUrl(
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
        // Move this to views
        $coreRoutes = array();
        $levelBoys = array(
            'U10B Core' => 'AYSO_U10B_Core',
            'U12B Core' => 'AYSO_U12B_Core',
            'U14B Core' => 'AYSO_U14B_Core',
            'U16B Core' => 'AYSO_U16B_Core',
            'U19B Core' => 'AYSO_U19B_Core',
        );
        $levelGirls = array(
            'U10G Core' => 'AYSO_U10G_Core',
            'U12G Core' => 'AYSO_U12G_Core',
            'U14G Core' => 'AYSO_U14G_Core',
            'U16G Core' => 'AYSO_U16G_Core',
            'U19G Core' => 'AYSO_U19G_Core',
        );
        foreach($levelBoys as $key => $level)
        {
            $coreRoutes['Boys'][$key] = $this->generateUrl('cerad_game__project__results_poolplay__show',array(
                '_project' => $_project,
                'level'    => $level,
            ));
        }
        foreach($levelGirls as $key => $level)
        {
            $coreRoutes['Girls'][$key] = $this->generateUrl('cerad_game__project__results_poolplay__show',array(
                '_project' => $_project,
                'level'    => $level,
            ));
        }
        $pools = $model->loadPools();
       
        // And render
        $tplData = array();
        $tplData['pools']  = $pools;
        $tplData['routes'] = $routes;
        $tplData['coreRoutes'] = $coreRoutes;
        return $this->regularResponse($request->get('_template'),$tplData);
    }
}
