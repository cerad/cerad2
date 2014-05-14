<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Team\Show;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ScheduleTeamShowController extends ActionController
{
    public function action(Request $request, ScheduleTeamShowModel $model, $form, $_format)
    {
        $form->handleRequest($request);
        if ($form->isValid()) 
        {   
            $model->process($request,$form->getData());
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
        }
        return;
        
        $games = $model->loadGames();
        
        // Spreadsheet
        if ($_format == 'xls')
        {
            $export   = new ScheduleTeamExportXLS();
            $response = new Response($export->generate($games));
        
            $outFileName = 'TeamSchedule' . date('Ymd-Hi') . '.xlsx';
        
            $response->headers->set('Content-Type',       'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"',$outFileName));
            return $response;
        }
        // csv processing
        if ($_format == 'csv')
        {
            // Should be a service but that means injecting it somehow
            $export   = new ScheduleTeamExportCSV();
            $response = new Response($export->generate($games));
        
            $outFileName = 'TeamSchedule' . date('Ymd-Hi') . '.csv';
        
            $response->headers->set('Content-Type',       'text/csv');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"',$outFileName));
            return $response;
        }
        
        // And render
        $tplData = array();
        $tplData['searchForm'] = $form->createView();
        $tplData['games'] = $games;
        return $this->regularResponse($request->get('_template'),$tplData);
    }
}
