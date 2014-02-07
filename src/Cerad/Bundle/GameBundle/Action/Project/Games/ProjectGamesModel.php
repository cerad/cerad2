<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Games;

use Symfony\Component\HttpFoundation\Request;
//  Symfony\Component\HttpFoundation\Response;
    
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/* ===========================================================
 * Initial attempt to make a model for dealing with a set of games
 * Currently geared to schedules but I keep tripping over different types of schedules
 * 
 * This one uses session to store criteria which in turn queries the games
 * 
 * Should I break the criteria out to it's own class?
 */
class GamesModel
{
    public    $user;
    public    $userPerson;
    public    $project;    // Will there be any other differences for projects?
    public    $projects;
    
    protected $gameRepo;
    protected $levelRepo;
    protected $personRepo;
    
    public $games    = array();
    public $criteria = array();
    
    const SESSION_SCHEDULE_OFFICIAL_QUERY_CRITERIA = 'scheduleOfficialQueryCriteria';
    
    public function __construct($project, $user, $userPerson, $personRepo, $levelRepo, $gameRepo)
    {   
        $this->user       = $user;
        $this->userPerson = $userPerson;
        $this->project    = $project;
        
        $this->gameRepo   = $gameRepo;
        $this->levelRepo  = $levelRepo;
        $this->personRepo = $personRepo;
    }
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }

    public function createModel(Request $request)
    {   
        // Game Query Criterisa
        $criteria = array();
        
        // Bit of optimization
        $criteria['wantGameTeams']     = true;
        $criteria['wantGameOfficials'] = true;
        
        // These are project independent
        $criteria['nums'] = array();
        
        $criteria['projects'] = array($this->project->getKey());
        
        $criteria['teamNamesFilter'    ] = array(); // TODO: Is this really needed?
        $criteria['fieldNamesFilter'   ] = array(); // TODO: verify plurals and names
        $criteria['officialNamesFilter'] = array();

        /* ==========================================================
         * Project specific search criteria
         * Multiple projects would need to be ignored?
         */
        $criteria['searches'] = $searches = $this->project->getSearches();

        foreach($searches as $key => $search)
        {
            $criteria[$key] = $search['default']; // Array of defaults
        }
        // Merge form session
        $session = $request->getSession();
        if ($session->has(self::SESSION_SCHEDULE_OFFICIAL_QUERY_CRITERIA))
        {
            $criteriaSession = $session->get(self::SESSION_SCHEDULE_OFFICIAL_QUERY_CRITERIA);
            $criteria = array_merge($criteria,$criteriaSession);
        }
        
        // No need to query if its it a post?
        $this->criteria = $criteria;
        if ($request->getMethod() != 'GET') return $this;
        
        // Convert programs/ages/genders to levels
        $levelKeys = $this->levelRepo->queryKeys($criteria);
        if (count($levelKeys))
        {
            // This is the most common case since mixing levels and program/age/gender is confusing
            if (!isset($criteria['levels'])) $criteria['levels'] = $levelKeys;
            else
            {
                // More for documentation
                $criteria['levels'] = array_merge($criteria['levels'],$levelKeys);
            }
        }
        // Query for the games
        $games = $this->gameRepo->queryGameSchedule($criteria);

        /* =========================================================
         * Processes and filters could also be injected or overridden
         */
        // Apply filters
        $gamesFiltered = $this->filterGames($games,$criteria);
       
        // My processing
        $gamesProcessed = $this->processGames($gamesFiltered,$this->userPerson);
        
        // Done
        $this->games = $gamesProcessed;
        
        return $$this;
    }
    /* =======================================================
     * Move the isAssignable functionality here?
     */
    public function processGames($userPerson,$games)
    {
        foreach($games as $game)
        {
            foreach($game->getOfficials() as $gameOfficial)
            {
                $gameOfficial->isUserUpdateable = $this->isGameOfficialUserUpdateable($userPerson,$game,$gameOfficial);
            }
        }
        return $games;
    }
    protected function isGameOfficialUserUpdateable($userPerson,$game,$gameOfficial)
    {
        if (!$gameOfficial->isUserAssignable()) return false;
        
        // Open slots or if needed slots can be assigned
        switch($gameOfficial->getAssignState())
        {
            case 'Open': 
            case 'IfNeeded': 
                return true;
            case 'Pending':
                return false;
        }
        // Update your own games but not some elses
        if ($gameOfficial->getPersonGuid() == $userPerson->getGuid()) return true;
        if ($gameOfficial->getPersonGuid()) return false;
        
        // Use the person plan info
        $personPlan = $userPerson->getPlan($game->getProjectKey(),false);
        if (!$personPlan || !$personPlan->getId()) return false;
        
        // Allow match by name?
        if ($gameOfficial->getPersonNameFull() == $personPlan->getPersonName()) return true;
        if ($gameOfficial->getPersonNameFull()) return false;
        
        // Verify willReferee
        if ($personPlan->getWillReferee() != 'Yes') return false;
        
        // Leaving willAttend for now so I come through
        
        return true;
    }
    // Really should be filter games but okay
    public function filterOfficials( array $games )
    {
      return $games;
    }

    public function getLink()
    {
      return 'cerad_tourn_schedule_official_list';
    }

    public function getFilename()
    {
      return 'RefSched' . date('YmdHi');
    }
}
