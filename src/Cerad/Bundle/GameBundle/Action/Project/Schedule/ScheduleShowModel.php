<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Cerad\Bundle\CoreBundle\Event\Level\FindProjectLevelsEvent;

use Cerad\Bundle\CoreBundle\Event\Person\FindProjectPersonTeamsEvent;

class ScheduleShowModel extends ActionModelFactory
{   
    public $project;
    public $criteria;
    
    public $teamKeys   = array();
    public $personKeys = array();
    
    protected $sessionName;
    protected $wantOfficials;
    
    protected $gameRepo;
    protected $levelRepo;
    
    public function __construct($gameRepo,$levelRepo,$sessionName = 'ScheduleShow',$wantOfficials = true)
    {
        $this->gameRepo  = $gameRepo;
        $this->levelRepo = $levelRepo;
        
        $this->sessionName = $sessionName;
        $this->wantOfficials = $wantOfficials;
    }
    public function create(Request $request)
    {   
        /* ===============================================
         * Pull the current person if the route asked for it
         */
        $userPerson = $request->attributes->get('userPerson');
        if ($userPerson) {
            foreach($userPerson->getPersonPersons() as $personPerson)
            {
                $this->personKeys[$personPerson->getChild()->getKey()] = true;
            }
        }
        
        // From form
        $criteria = array();

        $this->project = $project = $request->attributes->get('project');
        $criteria['projects'] = array($project->getKey());

        $criteria['programs'] = array();
        $criteria['genders' ] = array();
        $criteria['ages'    ] = array();
        
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
        if ($session->has($this->sessionName))
        {
            $criteriaSession = $session->get($this->sessionName);
            $criteria = array_merge($criteria,$criteriaSession);
        }
        // So much fun
        $this->criteria = $criteria;
        
        return $this;
    }
    public function process(Request $request,$criteria)
    {
        $this->criteria = $criteria;
        
        $request->getSession()->set($this->sessionName,$criteria);
    }
    public function loadGames()
    {
        $criteria = $this->criteria;
        
        // if ($this->program) $criteria = array('programs' => $this->program);
        
        // Could be an event
        $criteria['levelKeys'] = $this->levelRepo->queryKeys($criteria);
        
        $criteria['wantOfficials'] = $this->wantOfficials;
     
        $this->games = $this->gameRepo->queryGameSchedule($criteria);
        
        return $this->games;
    }
    /* =========================================================================
     * Get all the game ids for teams linked to the current person
     */
    protected function loadLevelKeys()
    {
        $criteria = $this->criteria;
        
        $event = new FindProjectLevelsEvent(
            $criteria['projects'],
            $criteria['programs'],
            $criteria['genders'],
            $criteria['ages']
        );
        $this->dispatcher->dispatch(FindProjectLevelsEvent::Find,$event);
        
        $levelKeys = $event->getLevelKeys();
        
      //echo 'Levels ' . implode(',',$levelKeys) . '<br />';
        
        return $levelKeys;
    }
    /* =========================================================================
     * Get all the game ids for teams linked to the current person
     */
    protected function loadTeamGameIds()
    {
        // NA if we don't have any
        if (count($this->personKeys) < 1) return array();
        
        // Restrict to selected dates
        $dates = $this->criteria['dates'];
        
        // Grab all the personTeams for the person
        $findPersonTeamsEvent = new FindProjectPersonTeamsEvent(
            $this->project,
            array_keys($this->personKeys),
            $dates
        );
        $this->dispatcher->dispatch(FindProjectPersonTeamsEvent::ByGuid,$findPersonTeamsEvent);
        
        $personTeams = $findPersonTeamsEvent->getPersonTeams();
        
        // Index list of team keys for the template
        $teamKeys = array();
        array_walk($personTeams, function($item) use (&$teamKeys) { 
            $teamKeys[$item->getTeamKey()] = true; 
        });
        $this->teamKeys = $teamKeys;
        
        $teamGameIds = $this->gameRepo->findAllIdsByTeamKeys(array_keys($teamKeys),$dates);
      //echo sprintf('Count Team Games %d<br />',count($teamGameIds));
        return $teamGameIds;
    }
}
