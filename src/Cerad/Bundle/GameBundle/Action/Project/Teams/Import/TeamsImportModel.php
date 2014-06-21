<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Import;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class TeamsImportModel extends ActionModelFactory
{   
    public $project;
    public $attachment;
    
    public $op = 0;
    public $commit = 0;
    
    protected $reader;
    protected $saver;
    
    public function __construct($reader,$saver)
    {   
        $this->reader = $reader;
        $this->saver  = $saver;
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
        
        $teams = $this->reader->read($this->project,$file->getPathname());

        $saveResults = $this->saver->save($teams,$this->commit,$this->op);
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