<?php

namespace Cerad\Bundle\GameBundle\Service\GameSchedule\Load;

class LoadTeams
{
    protected $teamRepo;
    
    public function __construct($teamRepo)
    {
        $this->teamRepo = $teamRepo;
    }
    protected function processTeam($teamx)
    {
        $num = (int)$teamx['num'];
        if (!$num) return;
        
        $levelKey   = $teamx['levelKey'];
        $projectKey = $teamx['projectKey'];
        
        $team = $this->teamRepo->findOneByProjectLevelNum($projectKey,$levelKey,$num);
        if (!$team)
        {
            $team = $this->teamRepo->createTeam();
            $team->setNum($num);
            
            $team->setRole  ('Physical');
            $team->setStatus('Active');
            
            $team->setLevelKey  ($levelKey);
            $team->setProjectKey($projectKey);
            
            $this->teamRepo->save($team);
        }
        $name = $teamx['name'];
        if (!$name) $name = sprintf('Team %02u',$num);
        $team->setName($name);
        $team->setPoints($teamx['points']);
    }
    public function process($teams)
    {
        echo sprintf("Loading teams %d\n",count($teams));
        foreach($teams as $team)
        {
            $this->processTeam($team);
        }
        $this->teamRepo->commit();
    }
}