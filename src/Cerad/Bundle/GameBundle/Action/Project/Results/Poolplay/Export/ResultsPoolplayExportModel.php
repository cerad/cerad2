<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results\Poolplay\Export;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\GameBundle\Event\FindResultsEvent; // PoolPlay results generator

class ResultsPoolplayExportModel extends ActionModelFactory
{
    protected $project;
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo  = $gameRepo;
    }
    public function create(Request $request)
    {   
        $this->project = $project = $request->attributes->get('project');
            
        return $this;
    }
    public function loadPools($levelKey)
    {        
        $criteria = array();
        
        $criteria['projectKeys'] = $this->project->getKey();
        $criteria['levelKeys']   = $levelKey;
        $criteria['groupTypes']  = 'PP';
        
        $games = $this->gameRepo->queryGameSchedule($criteria);
        
        // Need the results service
        $findResultsEvent = new FindResultsEvent($this->project);
        $this->dispatcher->dispatch(FindResultsEvent::EventName,$findResultsEvent);
        $results = $findResultsEvent->getResults();
        
        $pools = $results->getPools($games);
        
        return $pools;
    }
}
