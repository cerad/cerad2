<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\GameBundle\Action\Project\Schedule\ScheduleShowModel;

class ScheduleGameShowModel extends ScheduleShowModel
{   
    public function create(Request $request)
    {   
        parent::create($request);
        
        $program = $request->query->get('program');
        
        if ($program)
        {
            $this->criteria['programs'] = array($program);
            $this->criteria['genders' ] = array();
            $this->criteria['ages']     = array();
            $this->criteria['dates']    = array();
            
            $this->gameKeys   = array();
            $this->personKeys = array();
        }
        return $this;
    }
    public function loadGames()
    {
        /* ====================================================
         * Could add teams and persons search but I think only admin types will use this
         */
        
        // Level Games
        $levelKeys = $this->loadLevelKeys();
        
        $levelGameIds = $this->gameRepo->findAllIdsByProjectLevels(
            $this->project,
            $levelKeys,
            $this->criteria['dates']
        );
        
        $gameIds = array_merge($levelGameIds);
        
        $this->games = $this->gameRepo->findAllByGameIds($gameIds,false);
        
        return $this->games;
    }
}
