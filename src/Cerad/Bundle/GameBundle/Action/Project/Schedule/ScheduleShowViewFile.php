<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule;

use Cerad\Bundle\CoreBundle\Action\ActionView;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleShowViewFile extends ActionView
{
    protected $dumper;
    protected $prefix;
    
    public function __construct($dumper,$prefix = 'Schedule')
    {
        $this->dumper = $dumper;
        $this->prefix = $prefix;
    }
    public function renderResponse(Request $request)
    {   
        $model = $request->attributes->get('model');
        $games = $model->loadGames();
        
        $dumper = $this->dumper;
        
        $response = new Response($dumper->dump($games));
        
        $outFileName = $this->prefix . date('Ymd-Hi') . '.' . $dumper->getFileExtension();
        
        $response->headers->set('Content-Type', $dumper->getContentType());
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"',$outFileName));
        
        return $response;
    }
}
