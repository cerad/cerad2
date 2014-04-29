<?php
namespace Cerad\Bundle\GameBundle\Action\Project\GameReport\Update;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\GameBundle\Event\FindResultsEvent;

class GameReportUpdateModel extends ActionModelFactory
{  
    public $project;
    
    public $game;
    public $gameReport;
    public $homeTeamReport;
    public $awayTeamReport;
    
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo = $gameRepo;
    }
    public function create(Request $request)
    {   
        $this->project = $project = $request->attributes->get('project');
        $this->game    = $game    = $request->attributes->get('game');
        
        $this->gameReport     = $game->getReport();
        $this->homeTeamReport = $game->getHomeTeam()->getReport();
        $this->awayTeamReport = $game->getAwayTeam()->getReport();
        
        return $this;
    }
    public function process()
    {
        // Extract
        $game           = $this->game;
        $gameReport     = $this->gameReport;
        $homeTeamReport = $this->homeTeamReport;
        $awayTeamReport = $this->awayTeamReport;
        
        $homeTeam = $game->getHomeTeam();
        $awayTeam = $game->getAwayTeam();
        
        // Is it a clear operation?
        $gameReportStatus = $gameReport->getStatus();
        if ($gameReportStatus == 'Clear')
        {
            $game->setReport    (null);
            $homeTeam->setReport(null);
            $awayTeam->setReport(null);
            
            $this->gameRepo->flush();
            return $this;
        }
        // Need the results service
        $findResultsEvent = new FindResultsEvent($this->project);
        $this->dispatcher->dispatch(FindResultsEvent::EventName,$findResultsEvent);
        $results = $findResultsEvent->getResults();
       
        // Calculate points earned
        $results->calcPointsEarnedForTeam($homeTeamReport,$awayTeamReport);
        $results->calcPointsEarnedForTeam($awayTeamReport,$homeTeamReport);
        
        // Update status if goals were entered
        if (($homeTeamReport->getGoalsScored() !== null) && ($awayTeamReport->getGoalsScored() !== null))
        {
            if ($gameReportStatus == 'Pending') $gameReport->setStatus('Submitted');
            
            switch($game->getStatus())
            {
                case 'Normal':
                case 'In Progress':
                    $game->setStatus('Played');
                    break;
            }
        }
        // Save the results
        $game->setReport    ($gameReport);
        $homeTeam->setReport($homeTeamReport);
        $awayTeam->setReport($awayTeamReport);
        
        // And persist
        $this->gameRepo->commit();
        
        // Done
        return $this;
    }
}
