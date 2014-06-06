<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Export;

use Cerad\Bundle\CoreBundle\Action\ActionView;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamsExportView extends ActionView
{
    protected $export;
    protected $prefix;
    
    public function __construct($export, $prefix = 'Teams')
    {
        $this->export = $export;
        $this->prefix = $prefix;
    }
    public function renderResponse(Request $request)
    {   
        $model = $request->attributes->get('model');
        
        $export = $this->export;
        
        $response = new Response($export->generate($model));
        
        $outFileName = $this->prefix . date('Ymd-Hi') . '.' . $export->getFileExtension();
        
        $response->headers->set('Content-Type', $export->getContentType());
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"',$outFileName));
        
        return $response;
    }
}
