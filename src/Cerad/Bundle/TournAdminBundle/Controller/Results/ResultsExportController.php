<?php
namespace Cerad\Bundle\TournAdminBundle\Controller\Results;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

/* =================================================================
 * Currently only the pool play games are exported
 * But eventually want to add playoffs/champions as well
 */
class ResultsExportController extends MyBaseController
{
    public function exportAction(Request $request)
    {
        // Simple model
        $model = $this->createModel($request);
        if ($model['response']) return $model['response'];
                
        $pools   = $model['pools'];
        $project = $model['project'];
        
        $export = $this->get('cerad_tourn.results.export_xls');

        $export->generatePoolPlay($project,$pools);
            
        $outFileName = 'Results' . date('Ymd-Hi') . '.xls';
        
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
                
        // Pull all pool play games
        $gameRepo = $this->get('cerad_game.game_repository');
        $criteria = array();
        $criteria['projects' ]  = $project->getId();
        $criteria['groupTypes'] = 'PP';
        
        $games = $gameRepo->queryGameSchedule($criteria);
        
        // Process them to get the pools
        $resultsServiceId = sprintf('cerad_tourn.%s_results',$project->getResults());
        $results = $this->get($resultsServiceId);
        
        $pools = $results->getPools($games);
        
        $model['pools'] = $pools;
        
        // TODO: Add playoff games
        
        // Done
        return $model;
    }
}
