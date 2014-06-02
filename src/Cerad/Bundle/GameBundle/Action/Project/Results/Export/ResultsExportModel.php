<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results\Export;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\GameBundle\Event\FindResultsEvent; // PoolPlay results generator

class ResultsExportModel extends ActionModelFactory
{
    protected $criteria;
    protected $project;
    
    protected $gameRepo;
    protected $levelRepo;
    
    public function __construct($gameRepo,$levelRepo)
    {
        $this->gameRepo  = $gameRepo;
        $this->levelRepo = $levelRepo;
    }
    public function create(Request $request)
    {   
        $this->project  = $request->attributes->get('project');
        $this->criteria = $request->query->all();

        return $this;
    }
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
    public function loadPools($levelKey)
    {        
        $criteria = array();
        
        $criteria['projectKeys'] = $this->project->getKey();
        $criteria['levelKeys']   = $levelKey;
        $criteria['groupTypes']  = 'PP';
        $criteria['wantOfficials'] = false;
        
        $games = $this->gameRepo->queryGameSchedule($criteria);
        
        // Need the results service
        $findResultsEvent = new FindResultsEvent($this->project);
        $this->dispatcher->dispatch(FindResultsEvent::EventName,$findResultsEvent);
        $results = $findResultsEvent->getResults();
        
        $pools = $results->getPools($games);
        
        return $pools;
    }
    public function loadGames($levelKey,$type)
    {
        $criteria['projectKeys'] = $this->project->getKey();
        $criteria['levelKeys']   = $levelKey;
        $criteria['groupTypes']  = $type;
        
        $games = $this->gameRepo->queryGameSchedule($criteria);
        return $games;
    }
}
