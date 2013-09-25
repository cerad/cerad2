<?php
namespace Cerad\Bundle\TournBundle\Controller\GameReport;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class GameReportUpdateController extends MyBaseController
{
    public function updateAction(Request $request)
    {
        // Simple model
        $model = $this->createModel($request);
        if ($model['response']) return $model['response'];
        
        $form = $this->createModelForm($model);
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model = $form->getData();
            
            $this->processModel($model);
            
            return $this->redirect('cerad_tourn_game_report_update',array('num' => $model['game']->getNum()));
        }
        
        $tplData = array();
        $tplData['form']    = $form->createView();
        
        $tplData['game']    = $model['game'];
        $tplData['project'] = $model['project'];
        
        return $this->render('@CeradTourn/GameReport/Update/GameReportUpdateIndex.html.twig', $tplData);
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
        $project = $this->getProject();
        $resultsServiceId = sprintf('cerad_tourn.%s_results',$project->getResults());
        $results = $this->get($resultsServiceId);

        $results->calcPointsEarnedForTeam($homeTeamReport,$awayTeamReport);
        $results->calcPointsEarnedForTeam($awayTeamReport,$homeTeamReport);
        
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
        
        // Game comes from request
        $num = $request->get('num');
        
        $gameRepo = $this->get('cerad_game.game_repository');
        $game = $gameRepo->findOneByProjectNum($project->getId(),$num);
        if (!$game)
        {
            $model = array();
            $model['response'] = $this->redirect('cerad_tourn_welcome');
            return $model;
        }
        // Assorted report sections
        $model['game']       = $game;
        $model['gameReport'] = $game->getReport();
        
        $model['homeTeamReport'] = $game->getHomeTeam()->getReport();
        $model['awayTeamReport'] = $game->getAwayTeam()->getReport();
        
        return $model;
    }
    /* ==========================================
     * Hand crafted form
     */

    public function createModelForm($model)
    {
        $form = $this->createForm('cerad_game_report_update_master',$model);
        return $form;        
    }
}
