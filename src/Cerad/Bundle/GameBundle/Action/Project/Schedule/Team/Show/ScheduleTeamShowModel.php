<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Team\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class ScheduleTeamShowModel extends ActionModelFactory
{
    const SessionCriteria = 'ScheduleTeamShow';
    
    public $project;
    public $criteria;
    
    protected $gameRepo;
    protected $teamRepo;
    protected $levelRepo;
    
    public function __construct($gameRepo,$levelRepo,$teamRepo)
    {
        $this->gameRepo  = $gameRepo;
        $this->teamRepo  = $teamRepo;
        $this->levelRepo = $levelRepo;
    }
    public function getPrograms()
    {
        $programs = $this->project->getPrograms();
        print_r($programs); die();
        
        $programs = array();
        $searches = $this->project->getSearches();
        $programSearch = $searches['programs'];
        
        foreach($programSearch['choices'] as $key => $value)
        {
            if ($key != 'All') $programs[] = $key;
        }
        print_r($programs); die();
    }
    public function create(Request $request)
    {   
        $criteria = array();

        $this->project = $project = $request->attributes->get('project');
        $criteria['projects'] = array($project->getKey());

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
    public function process(Request $request,$criteria)
    {
        $request->getSession()->set(self::SessionCriteria,$criteria);
    }
    public function loadGames()
    {        
        $criteria = $this->criteria;
        
        // Could be an event
        $physicalTeamIds = array();
        $programs = $this->project->getPrograms();
        foreach($programs as $program)
        {
            $physicalTeamIds = array_merge($physicalTeamIds,$criteria[$program . 'Teams']);
        }
        if (count($physicalTeamIds) < 1) return array();
        
        $criteria['physicalTeamIds'] = $physicalTeamIds;
        
        $this->games = $this->gameRepo->queryGameSchedule($criteria);
        
        return $this->games;
    }
    /* ==========================================================
     * Or shoud this be loadTeams and let the form make it into choices
     */
    public function loadTeamChoices($program)
    {
        $levelKeys = $this->levelRepo->queryKeys(array('programs' => $program));

        $teams = $this->teamRepo->findAllByProjectLevels($this->project->getKey(),$levelKeys);
        $teamChoices = array();

        foreach($teams as $team)
        {
            $teamChoices[$team->getId()] = sprintf('%s Team %02d %s',
                $team->getLevelKey(),$team->getNum(),$team->getName());
        }
        return $teamChoices;
        
        //echo sprintf("Team Count : %d",count($teams)); die();
    }
}
