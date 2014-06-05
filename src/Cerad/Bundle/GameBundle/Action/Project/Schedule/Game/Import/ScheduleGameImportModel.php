<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Import;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class ScheduleGameImportModel extends ActionModelFactory
{   
    public $project;
    public $attachment;
    
    public $commit = 0;
    
    protected $importer;
    
    public function __construct($importer)
    {   
        $this->importer = $importer;
    }
        
    /* =====================================================
     * Process a posted model
     * Turn everything over to the workflow
     */
    public function process()
    {   
        $file = $this->attachment;
        
      //echo sprintf("Max file size %d %d Valid: %d, Error: %d<br />\n",
      //    $file->getMaxFilesize(),$file->getClientSize(),$file->isValid(), $file->getError());
        
        $importFilePath = $file->getPathname();
        $clientFileName = $file->getClientOriginalName();
        
        $params['project']  = $this->project;
        $params['filepath'] = $importFilePath;
        $params['basename'] = $clientFileName;
        
        $params['commit'] = $this->commit;
        
        // TODO:  This should load the data as an array then save it
        $results = $this->importer->process($params);

        return $results;
    }
    public function create(Request $request)
    {   
        $requestAttrs = $request->attributes;
        
        $this->project = $project = $requestAttrs->get('project');
                
        return $this;
    }
}