<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Export;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class TeamsExportModel extends ActionModelFactory
{
    protected $project;
    
    protected $teamRepo;
    protected $levelRepo;
    
    public function __construct($teamRepo,$levelRepo)
    {
        $this->teamRepo  = $teamRepo;
        $this->levelRepo = $levelRepo;
    }
    public function create(Request $request)
    {   
        $this->project = $request->attributes->get('project');
        return $this;
    }
    public function loadTeams($program)
    {
        $levelKeys = $this->levelRepo->queryKeys(array('programs' => $program));
        
        return $this->teamRepo->findAllByProjectLevels($this->project->getKey(),$levelKeys);
    }
    public function getPrograms() { return array('Core','Extra'); }
}
