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
    
    protected $importer;
    
    protected $gameRepo;
    
    public function __construct(AssignWorkflow $workflow, $gameRepo, $importer)
    {   
        $this->workflow = $workflow;
        $this->gameRepo = $gameRepo;
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
        
        $params['state' ] = $this->state;
        $params['verify'] = $this->verify;
        $params['commit'] = $this->commit;
        
        $results = $this->importer->process($params);

        return $results;
    }
    /* =========================================================================
     * Also holds logic to allow signing up for this particular game slot?
     */
    public function create(Request $request)
    {   
        $requestAttrs = $request->attributes;
        
        $this->project = $project = $requestAttrs->get('project');
                
        return $this;
    }
}