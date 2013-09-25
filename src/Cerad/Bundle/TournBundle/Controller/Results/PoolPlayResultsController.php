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
    protected function processModel($model)
    { 
        // Extract
        $game           = $model['game'];
        $gameReport     = $model['gameReport'];
        $homeTeamReport = $model['homeTeamReport'];
        $awayTeamReport = $model['awayTeamReport'];
        
        $homeTeam = $game->getHomeTeam();
        $awayTeam = $game->getAwayTeam();
        
        // Is it a clear operation?
        $gameReportStatus = $gameReport->getStatus();
        if ($gameReportStatus == 'Clear')
        {
            $gameReport->clear();
            $homeTeamReport->clear();
            $awayTeamReport->clear();
            $gameReportStatus = null;
            
            // Should be okay to let this fall through
        }
        // Calculate points earned
        $results = $this->get('cerad_tourn.s5games_results');
        $results->calcPointsEarnedForTeam($game,$homeTeamReport,$awayTeamReport);
        $results->calcPointsEarnedForTeam($game,$awayTeamReport,$homeTeamReport);
        
        // Update status if goals were entered
        if ($homeTeamReport->getGoalsScored() !== null)
        {
            if ($gameReportStatus == 'Pending') $gameReport->setStatus('Submitted');
            
            switch($game->getStatus())
            {
                case 'Normal':
                case 'In Progress':
                    $game->setStatus('Played');
                    break;
            }
        }
        // Save the results
        $game->setReport    ($gameReport);
        $homeTeam->setReport($homeTeamReport);
        $awayTeam->setReport($awayTeamReport);
        
        // And persist
        $gameRepo = $this->get('cerad_game.game_repository');
        $gameRepo->save($game);
        $gameRepo->commit();
        
        // Done
        return $model;
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
        $criteria['projects' ] = $project->getId();
        $criteria['levels'   ] = $div;
        $criteria['isPool'   ] = true; // Implement Later
        $criteria['groupType'] = 'PP'; // Implement Later
        
        $games = $gameRepo->queryGameSchedule($criteria);
        
        $results = $this->get('cerad_tourn.s5games_results');
        
        $pools = $results->getPools($games);
        
        $model['pools'] = $pools;
        
        return $model;
    }
}
