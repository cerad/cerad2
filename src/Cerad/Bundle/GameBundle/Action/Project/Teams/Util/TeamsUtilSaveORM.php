<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Util;

class TeamsUtilSaveORMResults
{
    public $commit = false;
    
    public $basename;
    
    public $total    = 0;
    public $created  = 0;
    public $updated  = 0;
    public $deleted  = 0;
}
class TeamsUtilSaveORM
{
    protected $results;
    
    protected $teamRepo;
    protected $gameRepo;
        
    public function __construct($teamRepo,$gameRepo)
    {
        $this->teamRepo = $teamRepo;
        $this->gameRepo = $gameRepo;
    }
    /* =============================================
     * TODO: Implement delete with negative number
     */
    protected function saveTeam($teamx)
    {   
        $results = $this->results;
        
        $num        = $teamx['num'];
        $levelKey   = $teamx['levelKey'];
        $projectKey = $teamx['projectKey'];
        
        $team = $this->teamRepo->findOneByProjectLevelNum($projectKey,$levelKey,$num);
        
        if (!$team)
        {
            $team = $this->teamRepo->createTeam();
            $team->setNum       ($num);
            $team->setLevelKey  ($levelKey);
            $team->setProjectKey($projectKey);
            
            $team->setName  ($teamx['name']);
            $team->setOrgKey($teamx['region']);
            $team->setPoints($teamx['points']);
            
            $results->created++;
            $this->teamRepo->persist($team);
            return;
        }
        $changed = false;

        if ($teamx['name'] != $team->getName())
        {
            // TODO: Need to propogate name changes to the game_team
            // Or maybe send an event?
            $team->setName($teamx['name']);
            if (!$changed) $results->updated++;
            $changed = true;
        }
        if ($teamx['region'] != $team->getOrgKey())
        {
            $team->setOrgKey($teamx['region']);
            if (!$changed) $results->updated++;
            $changed = true;
        }
        if ($teamx['points'] != $team->getPoints())
        {
            $team->setPoints($teamx['points']);
            if (!$changed) $results->updated++;
            $changed = true;
        }
        return;
    }
    /* ==============================================================
     * Main entry point
     */
    public function save($teams,$commit = false)
    {
        $this->results = $results = new TeamsUtilSaveORMResults();
        
        $results->commit = $commit;
        $results->total = count($teams);
        
        foreach($teams as $team)
        {
            $this->saveTeam($team);
        }
         
        if ($results->commit) $this->teamRepo->commit();
        
        return $results;
    }
}
?>
