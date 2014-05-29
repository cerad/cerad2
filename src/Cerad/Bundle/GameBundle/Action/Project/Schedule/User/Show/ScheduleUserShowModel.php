<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\User\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class ScheduleUserShowModel extends ActionModelFactory
{   
    public $project;
    public $personGuid;

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
        $criteria = array(
            'projects'      => $this->project->getKey(),
            'personGuids'   => $this->personGuid,
            'wantOfficials' => true,
        );
             
        $this->games = $this->gameRepo->queryGameSchedule($criteria);
        
        return $this->games;
    }
}
