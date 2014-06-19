<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\User\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\CoreBundle\Event\Person\FindProjectPersonTeamsEvent;

class ScheduleUserShowModel extends ActionModelFactory
{   
    public $project;
    public $personGuid;
    
    public $teamKeys;
    public $personKeys;
    
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo = $gameRepo;
    }
    public function create(Request $request)
    {   
        $this->project = $request->attributes->get('project');
        
        $user = $request->attributes->get('user');
        
        $this->personGuid = $user->getPersonGuid();
                
        return $this;
    }
    public function process(Request $request)
    {
        return;
    }
    public function loadGames()
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
