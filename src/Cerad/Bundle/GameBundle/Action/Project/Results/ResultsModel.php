<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\GameBundle\Event\FindResultsEvent;

/* ================================================================
 * 10 June 2014
 * Started with working Poolplay model
 * Added in functionality from working export model
 * 
 * Currently used for both show and export
 */
class ResultsModel extends ActionModelFactory
{
    public $show; // select,help,games,teams(aka standings)
    public $project;
    
    public $games;
    public $pools;
    
    protected $poolKey;
    protected $levelKey;
    
    protected $gameRepo;
    protected $levelRepo;
    
    public function __construct($gameRepo,$levelRepo)
    {
        $this->gameRepo  = $gameRepo;
        $this->levelRepo = $levelRepo;
    }
    public function create(Request $request)
    {   
        $this->project  = $project = $request->attributes->get('project');
        
        // TODO: Alternate means of getting levels
        $this->programs = $request->query->get('programs');
        $this->genders  = $request->query->get('genders');
        $this->ages     = $request->query->get('agees');
        $this->divs     = $request->query->get('divs');
        
        // Currently implemented, maybe do the level key processing here
        $this->levelKey = $request->query->get('level');
        $this->poolKey  = $request->query->get('pool');
        $this->show     = $request->query->get('show');
        
        return $this;
    }
    public function loadPools($levelKey = null)
    {        
        // Don't allow loading more than one level at a time
        $levelKey = $levelKey ? $levelKey : $this->levelKey;
        if (!$levelKey) return array();
        
        $criteria = array();
        
        $criteria['projectKeys'] = $this->project->getKey();
        $criteria['levelKeys']   = $levelKey;
        $criteria['groupTypes']  = 'PP';
        $criteria['wantOfficials'] = false;
        
//print_r($criteria); die();        
        $games = $this->gameRepo->queryGameSchedule($criteria);
        
        // Filter here, be sort of nice if the query could do this
        if ($this->poolKey)
        {
            $poolKey    = $this->poolKey;
            $poolKeyLen = strlen($poolKey) * -1;
            
            $gamesFiltered = array();
            foreach($games as $game)
            {
                $groupKey = $game->getGroupKey();
                if (substr($groupKey,$poolKeyLen) == $poolKey)
                {
                    $gamesFiltered[] = $game;
                }
            }
            $games = $gamesFiltered;
        }
        // Need the results service
        $findResultsEvent = new FindResultsEvent($this->project);
        $this->dispatcher->dispatch(FindResultsEvent::EventName,$findResultsEvent);
        $results = $findResultsEvent->getResults();
        
        $this->pools = $results->getPools($games);
        
        return $this->pools;
    }
    // TODO: Use ProjectLevels
    // Only called by the export routine
    // TODO: Might want to implement criteria a bit better
    public function getLevels()
    {   
        $criteria = array();
        $criteria['projects'] = $this->project->getKey();
        $criteria['programs'] = $this->programs;
        $criteria['genders']  = $this->genders;
        $criteria['ages']     = $this->ages;
   
        $levelKeys = $this->levelRepo->queryKeys($criteria);
        
        if (count($levelKeys) < 1) return $this->levelRepo->findAll();
        
        $levels = array();
        foreach($levelKeys as $levelKey)
        {
            $levels[] = $this->levelRepo->find($levelKey);
        }
        return $levels;
    }
    // For playoffs and sportsmanship
    public function loadGames($groupTypes, $levelKeys = null)
    {
        // Don't allow loading the entire project unless that is what we really want
        $levelKeys = $levelKeys ? $levelKeys : $this->levelKey;
        if (!$levelKeys) return array();
        
        $criteria['projectKeys'] = $this->project->getKey();
        $criteria['levelKeys']   = $levelKeys;
        $criteria['groupTypes']  = $groupTypes;
        
        $games = $this->gameRepo->queryGameSchedule($criteria);
        return $games;
    }
    // Sportsmanship
    public function loadSportsmanshipTeams($groupTypes,$levelKeys = null)
    {
        $games = $this->loadGames($groupTypes,$levelKeys);
        $teams = array();
        foreach($games as $game)
        {
            foreach($game->getTeams() as $gameTeam)
            {
                $team = $gameTeam->getTeam();
                if ($team)
                {
                    $teamId = $team->getId();
                    if (!isset($teams[$teamId])) $teams[$teamId] = array(
                        'game' => $game,
                        'team' => $team, 
                        'sp'   => 0
                    );
                    $teams[$teamId]['sp'] += $gameTeam->getReport()->getSportsmanship();
                }
            }
        }
        usort($teams,array($this,'compareTeamSportsmanship'));
        return $teams;
    }
    public function compareTeamSportsmanship($team1,$team2)
    {
        if ($team1['sp'] < $team2['sp']) return  1;
        if ($team1['sp'] > $team2['sp']) return -1;
        return 0;
    }
    // TODO: The levelRepo should do this
    public function genLevelKey($program,$gender,$age)
    {
        $program = ucfirst(strtolower($program));
        $gender  = ucfirst(substr($gender,0,1));
        $age     = ucfirst($age);
        return sprintf('AYSO_%s%s_%s',$age,$gender,$program);
    }
}
