<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class ScheduleShowModel extends ActionModelFactory
{   
    public $project;
    public $criteria;
    
    protected $sessionName;
    protected $wantOfficials;
    
    protected $gameRepo;
    protected $levelRepo;
    
    protected $program = null;
    
    public function __construct($gameRepo,$levelRepo,$sessionName = 'ScheduleShow',$wantOfficials = true)
    {
        $this->gameRepo  = $gameRepo;
        $this->levelRepo = $levelRepo;
        
        $this->sessionName = $sessionName;
        $this->wantOfficials = $wantOfficials;
    }
    public function create(Request $request)
    {   
        /* =============================================
         * Check to see if program was passed as a request parameter
         */
        if ($request->query->has('program'))
        {
            $this->program = $request->query->get('program');
        }
        
        // From form
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
        if ($session->has($this->sessionName))
        {
            $criteriaSession = $session->get($this->sessionName);
            $criteria = array_merge($criteria,$criteriaSession);
        }
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
        
        if ($this->program) $criteria = array('programs' => $this->program);
        
        // Could be an event
        $criteria['levelKeys'] = $this->levelRepo->queryKeys($criteria);
        
        $criteria['wantOfficials'] = $this->wantOfficials;
     
        $this->games = $this->gameRepo->queryGameSchedule($criteria);
        
        return $this->games;
    }
}
