<?php
namespace Cerad\Bundle\TournAdminBundle\Controller\Schedule\Games;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

/* =================================================================
 * Designed to be used by the administrator
 */
class ScheduleGamesExportController extends MyBaseController
{
    public function exportAction(Request $request)
    {
        // Simple model
        $model = $this->createModel($request);
        if ($model['response']) return $model['response'];
                
        $games   = $model['games'];
        $project = $model['project'];
        
        $export = $this->get('cerad_tourn_admin.schedule_games.export_xls');

        $export->generate($project,$games);
            
        $outFileName = 'ScheduleGames' . date('Ymd-Hi') . '.xlsx';
        
        $response = new Response();
        $response->setContent($export->getBuffer());
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$outFileName\"");
            
        return $response;
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
                
        // All games for now, add search later
        $gameRepo = $this->get('cerad_game.game_repository');
        $criteria = array();
        $criteria['projects' ] = $project->getId();
        
        $games = $gameRepo->queryGameSchedule($criteria);
                
        $model['games'] = $games;
        
        // Done
        return $model;
    }
}
