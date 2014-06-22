<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Team\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class ScheduleTeamShowModel extends ActionModelFactory
{
    const SessionCriteria = 'ScheduleTeamShow';
    
    public $project;
    public $criteria;
    
    public $teamKeys   = array();
    public $personKeys = array();
    
    protected $gameRepo;
    protected $teamRepo;
    protected $levelRepo;
    
    public function __construct($gameRepo,$levelRepo,$teamRepo)
    {
        $this->gameRepo  = $gameRepo;
        $this->teamRepo  = $teamRepo;
        $this->levelRepo = $levelRepo;
    }
    public function create(Request $request)
    {   
        $criteria = array();

        $this->project = $project = $request->attributes->get('project');
        
      //$criteria['projects'] = array($project->getKey());

        $programs = $project->getPrograms();
        foreach($programs as $program)
        {
            $criteria[$program . 'Teams' ] = array();
        }
        
        // Merge form session
        $session = $request->getSession();
        if ($session->has(self::SessionCriteria))
        {
            $criteriaSession = $session->get(self::SessionCriteria);
            $criteria = array_merge($criteria,$criteriaSession);
        }
        $this->criteria = $criteria;
        
        return $this;
    }
    public function process(Request $request, $criteria)
    {
        $request->getSession()->set(self::SessionCriteria,$criteria);
    }
    public function loadGames()
    {
        $criteria = $this->criteria;
      
        // Different select for each program
        $teamKeys = array();
        $programs = $this->project->getPrograms();
        foreach($programs as $program)
        {
            $teamKeys = array_merge($teamKeys,$criteria[$program . 'Teams']);
        }

        // Need gameIds for each physical team
        $gameIds = $this->gameRepo->findAllIdsForTeamKeys($teamKeys);
        
        // Then the games
        $this->games = $this->gameRepo->findAllByGameIds($gameIds);
        
        return $this->games;
    }
    /* ==========================================================
     * Or shoud this be loadTeams and let the form make it into choices
     */
    public function loadTeamChoices($program)
    {
        $levelKeys = $this->levelRepo->queryKeys(array('programs' => $program));

        $teams = $this->teamRepo->findAllByProjectLevels($this->project->getKey(),$levelKeys);
        
        $teamChoices = array(0 => 'None');

        foreach($teams as $team)
        {   
            $teamChoices[$team->getKey()] = $team->getDesc();
        }
        return $teamChoices;
    }
}
