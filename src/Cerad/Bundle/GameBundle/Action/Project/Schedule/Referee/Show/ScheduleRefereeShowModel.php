<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Referee\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\GameBundle\Action\Project\Schedule\ScheduleShowModel;

use Cerad\Bundle\CoreBundle\Event\Person\FindProjectPersonTeamsEvent;

class ScheduleRefereeShowModel extends ScheduleShowModel
{   
    public $personGuid;
    
    public function create(Request $request)
    {   
        parent::create($request);
        
        $user = $request->attributes->get('user');
        
        $this->personGuid = $user->getPersonGuid();
                
        return $this;
    }
    public function loadGames()
    {
        // Filter by dates
        $dates = $this->criteria['dates'];
        
        $project = $this->project;
        
        // Grab all the personTeams for the person
        $findPersonTeamsEvent = new FindProjectPersonTeamsEvent($project,array($this->personGuid),$dates);
        $this->dispatcher->dispatch(FindProjectPersonTeamsEvent::ByGuid,$findPersonTeamsEvent);
        $personTeams = $findPersonTeamsEvent->getPersonTeams();
        
        $teamKeys = array();
        array_walk($personTeams, function($item) use (&$teamKeys) { 
            $teamKeys[$item->getTeamKey()] = true; 
        });
        $this->teamKeys = $teamKeys; // For Templates
        
        $teamGameIds = $this->gameRepo->findAllIdsByTeamKeys(array_keys($teamKeys),$dates);
      //echo sprintf('Count Team Games %d<br />',count($teamGameIds));
        
        $this->personKeys = $personKeys = array($this->personGuid => true);
        $personGameIds = $this->gameRepo->findAllIdsByProjectPersonKeys($project,array_keys($personKeys));
        
        $criteria = array(
            'projects'      => $project->getKey(),
            'personGuids'   => $this->personGuid,
            'teamKeys'      => $teamKeys,
            'wantOfficials' => true,
        );
             
        $gameIds = array_merge($teamGameIds,$personGameIds);
        
        
      //$this->games = $this->gameRepo->findAllByGameIds($gameIds,true);
        return parent::loadGames();
        
        return $this->games;
    }
}
