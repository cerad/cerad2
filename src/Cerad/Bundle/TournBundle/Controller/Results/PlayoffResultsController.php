<?php
namespace Cerad\Bundle\TournBundle\Controller\Results;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class PlayoffResultsController extends MyBaseController
{    
    const SESSION_RESULTS_PLAYOFF_DIV  = 'results_playoff_div';
    
    public function resultsAction(Request $request)
    {
        // Simple model
        $model = $this->createModel($request);
        if (isset($model['response'])) return $model['response'];
                
        $tplData = array();
        
        $tplData['gamesSF'] = $model['gamesSF'];
        $tplData['gamesCM'] = $model['gamesCM'];
        $tplData['gamesFM'] = $model['gamesFM'];
        $tplData['project'] = $model['project'];
        
        return $this->render($request->get('_template'), $tplData);
    }
    /* ===============================================
     * Assorted report objects
     */
    protected function createModel(Request $request)
    {
        // Back and forth on this
        $model = array();
        
        // Need current project
        $project = $this->getProject();
        $model['project'] = $project;
        
        // Optional Division comes from request or session
        $session = $request->getSession();
        $div = $request->get('div');
        if (!$div) 
        {
            // Maybe should do a redirect here?
            $div = $session->get(self::SESSION_RESULTS_PLAYOFF_DIV);
        }
        $session->set(self::SESSION_RESULTS_PLAYOFF_DIV,$div);
                
        // Pull the games
        $gameRepo = $this->get('cerad_game.game_repository');
        $criteria = array();
        $criteria['projects' ] = $project->getId();
        if ($div) $criteria['levels'] = $div;
        
        $criteria['groupTypes'] = 'SF';
        $model['gamesSF'] = $gameRepo->queryGameSchedule($criteria);
        
        $criteria['groupTypes'] = 'CM';
        $model['gamesCM'] = $gameRepo->queryGameSchedule($criteria);
        
        $criteria['groupTypes'] = 'FM';
        $model['gamesFM'] = $gameRepo->queryGameSchedule($criteria);
                
        return $model;
    }
}
