<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results\Poolplay\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\GameBundle\Event\FindResultsEvent;

class ResultsPoolplayShowModel extends ActionModelFactory
{
    public $project;
    public $games;
    public $pools;
    
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
        
        // Need to do some stuff with request parameters and maybe session data        
        return $this;
    }
    public function loadPools()
    {        
        if (!$this->levelKey) return array();
        
        $criteria = array();
        
        $criteria['projectKeys'] = $this->project->getKey();
        $criteria['levelKeys']   = $this->levelKey;
        $criteria['groupTypes']  = 'PP';
        $criteria['wantOfficials'] = false;
        
//print_r($criteria); die();        
        $games = $this->gameRepo->queryGameSchedule($criteria);
        
        // Filter here
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
}
