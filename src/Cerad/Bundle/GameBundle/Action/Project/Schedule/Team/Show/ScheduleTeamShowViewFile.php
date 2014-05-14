<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Team\Show;

use Cerad\Bundle\CoreBundle\Action\ActionView;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleTeamShowViewFile extends ActionView
{
    protected $export;
    
    public function __construct($export)
    {
        $this->export = $export;
    }
    public function renderResponse(Request $request)
    {   
        $model = $request->attributes->get('model');
        $games = $model->loadGames();
        
        $export = $this->export;
        
        $response = new Response($export->generate($games));
        
        $outFileName = 'TeamSchedule' . date('Ymd-Hi') . '.' . $export->getFileExtension();
        
        $response->headers->set('Content-Type', $export->getContentType());
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"',$outFileName));
        
        return $response;
    }
}
