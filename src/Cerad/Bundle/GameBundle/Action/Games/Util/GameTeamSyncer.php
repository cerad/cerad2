<?php

namespace Cerad\Bundle\GameBundle\Action\Games\Util;

class GameTeamSyncerResults
{
    public $commit = false;
    
    public $basename;
    
    public $total = 0;
    public $updated;
}
/* ======================================================
 * For each game team, link the associated pyhsical team
 */
class GameTeamSyncer
{
    protected $results;
   
    protected $gameTeamRepo;
        
    public function __construct($gameTeamRepo)
    {
        $this->gameTeamRepo = $gameTeamRepo;
    }
    /* =============================================
     * TODO: Implement delete with negative number
     */
    protected function syncTeam($gameTeam)
    {   
        $team = $gameTeam->getTeam(false);
        $teamName = $team ? $team->getName() : null;
        
        if ($gameTeam->getName() == $teamName) return;
        
        $gameTeam->setName($teamName);
        $this->results->updated++;
        
    }
    /* ==============================================================
     * Main entry point
     */
    public function sync($project,$commit = false)
    {
        $this->projectKey = $project->getKey();
        
        $this->results = $results = new GameTeamSyncerResults();
        
        $gameTeams = $this->gameTeamRepo->findAllByProjectLevel($this->projectKey);
        
        $results->commit = $commit;
        $results->total = count($gameTeams);
        
        foreach($gameTeams as $gameTeam)
        {
            $this->syncTeam($gameTeam);
        }
        if ($results->commit) 
        {
            $this->gameTeamRepo->commit();
        }
        return $results;
    }
}
?>
