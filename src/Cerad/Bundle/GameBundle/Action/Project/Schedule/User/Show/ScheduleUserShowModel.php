<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\User\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Event\Person\FindProjectPersonTeamsEvent;

use Cerad\Bundle\GameBundle\Action\Project\Schedule\ScheduleShowModel;

class ScheduleUserShowModel extends ScheduleShowModel
{   
    // No form, Should never be called
    public function process(Request $request,$criteria) { return; }
    
    public function create(Request $request)
    {   
        parent::create($request);
        $this->criteria['dates'] = array();
        return $this;
    }
    public function loadGames()
    {
        $project = $this->project;
        
        // Person Teams
        $teamGameIds = $this->loadTeamGameIds();
        
        // Official Games
        $personGameIds = $this->gameRepo->findAllIdsByProjectPersonKeys(
            $project,
            array_keys($this->personKeys)
        );
        
        $gameIds = array_merge($teamGameIds,$personGameIds);
        
        $this->games = $this->gameRepo->findAllByGameIds($gameIds,true);
        
        return $this->games;
    }
    
    public function loadGamesOld()
    {
        $project = $this->project;
        
        // Grab all the personTeams for the person
        $findPersonTeamsEvent = new FindProjectPersonTeamsEvent($project,array($this->personGuid));
        $this->dispatcher->dispatch(FindProjectPersonTeamsEvent::ByGuid,$findPersonTeamsEvent);
        $personTeams = $findPersonTeamsEvent->getPersonTeams();
        
        $teamKeys = array();
        array_walk($personTeams, function($item) use (&$teamKeys) { 
            $teamKeys[$item->getTeamKey()] = true; 
        });
        $this->teamKeys = $teamKeys; // For Templates
        
        $teamGameIds = $this->gameRepo->findAllIdsByTeamKeys(array_keys($teamKeys));
        
        $this->personKeys = $personKeys = array($this->personGuid => true);
        $personGameIds = $this->gameRepo->findAllIdsByProjectPersonKeys($project,array_keys($personKeys));
        
        $criteria = array(
            'projects'      => $project->getKey(),
            'personGuids'   => $this->personGuid,
            'teamKeys'      => $teamKeys,
            'wantOfficials' => true,
        );
             
        $gameIds = array_merge($teamGameIds,$personGameIds);
        $this->games = $this->gameRepo->findAllByGameIds($gameIds,true);
        
        return $this->games;
    }
}
