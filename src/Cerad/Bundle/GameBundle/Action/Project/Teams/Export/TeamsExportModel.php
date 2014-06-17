<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Export;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class TeamsExportModel extends ActionModelFactory
{
    protected $project;
    
    protected $teamRepo;
    protected $levelRepo;
    protected $gameTeamRepo;
    
    public function __construct($teamRepo,$gameTeamRepo,$levelRepo)
    {
        $this->teamRepo     = $teamRepo;
        $this->levelRepo    = $levelRepo;
        $this->gameTeamRepo = $gameTeamRepo;
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
    public function findAllGameTeamsByTeam($team)
    {
        return $this->gameTeamRepo->findAllByTeam($team);
    }
    // Should be injected or come from project
    public function getPrograms() { return $this->project->getPrograms(); }
}
