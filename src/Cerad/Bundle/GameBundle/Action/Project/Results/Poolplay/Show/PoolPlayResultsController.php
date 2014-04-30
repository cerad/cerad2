<?php
namespace Cerad\Bundle\TournBundle\Controller\Results;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class PoolPlayResultsController extends MyBaseController
{
    const SESSION_RESULTS_POOLPLAY_DIV  = 'results_poolplay_div';
    const SESSION_RESULTS_POOLPLAY_POOL = 'results_poolplay_pool';
    
    public function resultsAction(Request $request)
    {
        // Simple model
        $model = $this->createModel($request);
        if ($model['response']) return $model['response'];
                
        $tplData = array();
        
        $tplData['pools']   = $model['pools'];
        $tplData['project'] = $model['project'];
        
        return $this->render('@CeradTourn/Results/PoolPlay/ResultsPoolPlayIndex.html.twig', $tplData);
    }
    /* ===============================================
     * Assorted report objects
     */
    protected function createModel(Request $request)
    {
        // Back and forth on this
        $model = array();
        $model['response'] = null;
        
        // Need current project
        $project = $this->getProject();
        $model['project'] = $project;
        
        // Division comes from request or session
        $session = $request->getSession();
        $div = $request->get('div');
        if (!$div) 
        {
            // Maybe should do a redirect here?
            $div = $session->get('SESSION_RESULTS_POOLPLAY_DIV');
        }
        if (!$div)
        {
            $model['pools'] = array();
            return $model;
        }
        $session->set('SESSION_RESULTS_POOLPLAY_DIV',$div);
        
        // Pull the games
        $gameRepo = $this->get('cerad_game.game_repository');
        $criteria = array();
        $criteria['projects' ]  = $project->getId();
        $criteria['levels'   ]  = $div;
        $criteria['groupTypes'] = 'PP';
        
        $games = $gameRepo->queryGameSchedule($criteria);
        
        $resultsServiceId = sprintf('cerad_tourn.%s_results',$project->getResults());
        $results = $this->get($resultsServiceId);
        
        $pools = $results->getPools($games);
        
        $model['pools'] = $pools;
        
        return $model;
    }
}
