<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Referee\Show;

//  Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\GameBundle\Action\Project\Schedule\ScheduleShowModel;

class ScheduleRefereeShowModel extends ScheduleShowModel
{   
    public function loadGames()
    {
        // Filter by dates
        $dates = $this->criteria['dates'];
        
        $project = $this->project;
        
        // Person Teams
        $teamGameIds = $this->loadTeamGameIds();
        
        // Official Games
        $personGameIds = $this->gameRepo->findAllIdsByProjectPersonKeys(
            $project,
            array_keys($this->personKeys),
            $dates
        );
        
        // The real query
        $levelKeys = $this->loadLevelKeys();
        
        /*
        $criteria = array(
            'projects'      => $project->getKey(),
            'teamKeys'      => $this->teamKeys,
            'wantOfficials' => true,
        );
        */
        $gameIds = array_merge($teamGameIds,$personGameIds);
        
        
        $this->games = $this->gameRepo->findAllByGameIds($gameIds,true);
        
        return $this->games;
    }
}
