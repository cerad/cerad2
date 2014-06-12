<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByImport;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\CoreBundle\Event\FindOfficialsEvent;
use Cerad\Bundle\CoreBundle\Event\FindPersonPlanEvent;

use Cerad\Bundle\GameBundle\Action\Project\GameOfficials\Assign\AssignWorkflow;

class AssignByImportModel extends ActionModelFactory
{   
    public $attachment;
    public $project;
    
    public $state  = 'Pending';
    public $commit = 0;
    public $verify = 1;
    
    protected $workflow;
    
    protected $reader;
    protected $saver;
    
    protected $gameRepo;
    
    public function __construct(AssignWorkflow $workflow, $gameRepo, $reader, $saver)
    {   
        $this->workflow = $workflow;
        $this->gameRepo = $gameRepo;
        $this->reader   = $reader;
        $this->saver    = $saver;
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
        
        $games = $this->reader->read($file->getPathname(),$this->project);

        $saveResults = $this->saver->save($games,$this->commit,$this->state,$this->verify);
        
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