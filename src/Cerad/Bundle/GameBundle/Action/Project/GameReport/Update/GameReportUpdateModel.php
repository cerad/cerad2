<?php
namespace Cerad\Bundle\GameBundle\Action\Project\GameReport\Update;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class GameReportUpdateModel extends ActionModelFactory
{
    const SessionCriteria = 'RefereeScheduleShow';
    
    public $project;
    public $game;
    
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo  = $gameRepo;
    }
    public function create(Request $request)
    {   
        $this->project = $project = $request->attributes->get('project');
        $this->game    = $game    = $request->attributes->get('game');
        
        $this->gameReport     = $game->getReport();
        $this->homeTeamReport = $game->getHomeTeam()->getReport();
        $this->awayTeamReport = $game->getAwayTeam()->getReport();
        
        die('game report update model');
        
        $criteria = array();

        $this->project = $project = $request->attributes->get('project');
        $criteria['projects'] = array($project->getKey());

        $criteria['teams' ]  = array();
        $criteria['fields']  = array();
        
        $this->searches = $searches = $project->getSearches();
      
      //echo implode(',',array_keys($searches)); die();
        
        foreach($searches as $name => $search)
        {
            $criteria[$name] = $search['default']; // Array of defaults
        }
      //print_r($criteria); die();
        
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
        $this->criteria = $criteria;
        
        $request->getSession()->set(self::SessionCriteria,$criteria);
    }
    public function loadGames()
    {        
        $criteria = $this->criteria;
        
        // Could be an event
        $criteria['levelKeys'] = $this->levelRepo->queryKeys($criteria);
//print_r($criteria); die();        
        $this->games = $this->gameRepo->queryGameSchedule($criteria);
        
        return $this->games;
    }
}
