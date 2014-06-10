<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\GameBundle\Event\FindResultsEvent;

/* ================================================================
 * 10 June 2014
 * Started with working Poolplay model
 * Added in functionality from working export model
 */
class ResultsShowModel extends ActionModelFactory
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
    public function getLevels()
    {   
        $criteria = $this->criteria;
        $criteria['projects'] = $this->project->getKey();
        
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
    public function loadGames($levelKeys = null, $groupTypes = null)
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
    // For playoffs and sportsmanship
    public function loadTeams($levelKeys = null, $groupTypes = null)
    {
        $games = $this->loadGames($levelKeys,$groupTypes);
        
        // Don't allow loading the entire project unless that is what we really want
        $levelKeys = $levelKeys ? $levelKeys : $this->levelKey;
        if (!$levelKeys) return array();
        
        $criteria['projectKeys'] = $this->project->getKey();
        $criteria['levelKeys']   = $levelKeys;
        $criteria['groupTypes']  = $groupTypes;
        
        $games = $this->gameRepo->queryGameSchedule($criteria);
        return $games;
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
