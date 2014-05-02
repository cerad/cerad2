<?php

namespace Cerad\Bundle\GameBundle\Service\GameSchedule\Load;

class LinkTeams
{
    protected $gameRepo;
    protected $teamRepo;
    
    public function __construct($gameRepo,$teamRepo)
    {
        $this->gameRepo = $gameRepo;
        $this->teamRepo = $teamRepo;
    }
    protected function processTeam($teamx)
    {
        $num = (int)$teamx['num'];
        if (!$num) return;
        
        $levelKey   = $teamx['levelKey'];
        $projectKey = $teamx['projectKey'];
        
        // Team should always exist by now
        $team = $this->teamRepo->findOneByProjectLevelNum($projectKey,$levelKey,$num);
        if (!$team)
        {
            print_r($teamx);
            echo sprintf("*** Could not find team\n");
            die();
        }
        
        // Get game teams for project,level,groupSlot
        $groupSlot = $teamx['groupSlot'];
        if (!$groupSlot) return;
        
        $gameTeams = $this->gameRepo->findAllGameTeamsByProjectLevelGroupSlot($projectKey,$levelKey,$groupSlot);
        foreach($gameTeams as $gameTeam)
        {
            $gameTeam->setTeam($team);
            $gameTeam->setName($team->getName());
        }
        if (count($gameTeams))
        {
          //echo sprintf("Game Team Count %d For %s\n",count($gameTeams),$groupSlot);
          //die();
        }
    }
    public function process($teams)
    {
        echo sprintf("Linking teams %d\n",count($teams));
        foreach($teams as $team)
        {
            $this->processTeam($team);
        }
        $this->gameRepo->commit();
        $this->teamRepo->commit();
    }
}