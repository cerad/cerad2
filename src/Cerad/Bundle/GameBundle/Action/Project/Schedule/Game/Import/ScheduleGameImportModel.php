<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Import;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class ScheduleGameImportModel extends ActionModelFactory
{   
    public $project;
    public $attachment;
    
    public $commit = 0;
    
    protected $saver;
    protected $reader;
    
    public function __construct($reader,$saver)
    {   
        $this->saver  = $saver;
        $this->reader = $reader;
    }
    /* =====================================================
     * Process a posted model
     */
    public function process()
    {   
        $file = $this->attachment;
        
      //echo sprintf("Max file size %d %d Valid: %d, Error: %d<br />\n",
      //    $file->getMaxFilesize(),$file->getClientSize(),$file->isValid(), $file->getError());
        
        $games = $this->reader->read($file->getPathname(),$this->project);

        $saveResults = $this->saver->save($games,$this->commit);
        $saveResults->basename = $file->getClientOriginalName();
        
        return $saveResults;
        
    }
    public function create(Request $request)
    {   
        $requestAttrs = $request->attributes;
        
        $this->project = $project = $requestAttrs->get('project');
                
        return $this;
    }
}