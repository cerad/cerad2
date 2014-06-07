<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule;

use Cerad\Bundle\CoreBundle\Action\ActionView;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleShowViewFile extends ActionView
{
    protected $dumper;
    protected $prefix;
    protected $showOfficials;
    
    public function __construct($dumper,$prefix = 'Schedule',$showOfficials = true)
    {
        $this->dumper = $dumper;
        $this->prefix = $prefix;
        $this->showOfficials = $showOfficials;
    }
    public function renderResponse(Request $request)
    {   
        $model = $request->attributes->get('model');
        $games = $model->loadGames();
        
        $dumper = $this->dumper;
        
        $response = new Response($dumper->dump($games,$this->showOfficials));
        
        $outFileName = $this->prefix . date('Ymd-Hi') . '.' . $dumper->getFileExtension();
        
        $response->headers->set('Content-Type', $dumper->getContentType());
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"',$outFileName));
        
        return $response;
    }
}
