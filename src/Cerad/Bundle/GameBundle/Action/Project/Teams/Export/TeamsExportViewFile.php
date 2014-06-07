<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Export;

use Cerad\Bundle\CoreBundle\Action\ActionView;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamsExportViewFile extends ActionView
{
    protected $dumper;
    protected $prefix;
    
    public function __construct($dumper, $prefix = 'Teams')
    {
        $this->dumper = $dumper;
        $this->prefix = $prefix;
    }
    public function renderResponse(Request $request)
    {   
        $model = $request->attributes->get('model');
        
        $dumper = $this->dumper;
        
        $response = new Response($dumper->dump($model));
        
        $outFileName = $this->prefix . date('Ymd-Hi') . '.' . $dumper->getFileExtension();
        
        $response->headers->set('Content-Type', $dumper->getContentType());
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"',$outFileName));
        
        return $response;
    }
}
