<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Assignor\Summary;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\GameBundle\Action\Project\Schedule\ScheduleShowModel;

class ScheduleAssignorSummaryModel extends ScheduleShowModel
{   
    protected $gameRepo;
    protected $levelRepo;
    protected $personPlanRepo;
    
    public function __construct($gameRepo,$levelRepo,$personPlanRepo)
    {
        $this->gameRepo       = $gameRepo;
        $this->levelRepo      = $levelRepo;
        $this->personPlanRepo = $personPlanRepo;
    }
    public function create(Request $request)
    {   
        $this->program = $request->query->get('program');
        
        return $this;
    }
    public function loadOfficials()
    {
        // Must have a program
        if (!$this->program) return array();
        
         // Level Games
        $levelKeys = $this->levelRepo->queryKeys(array('programs' => array($this->program)));
        
        print_r($levelKeys); die();
        
        $levelGameIds = $this->gameRepo->findAllIdsByProjectLevels(
            $this->project,
            $levelKeys,
            $this->criteria['dates']
        );
        
        $gameIds = array_merge($levelGameIds);
        
        $games = $this->gameRepo->findAllByGameIds($gameIds,true);
        
        // Filter games here
        
        return $games;
    }
}
