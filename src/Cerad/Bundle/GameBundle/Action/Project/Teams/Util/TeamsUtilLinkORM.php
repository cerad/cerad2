<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Util;

class TeamsUtilLinkORMResults
{
    public $commit = false;
    
    public $basename;
    
    public $total    = 0;
    public $linked;
    public $missingTeams     = array();
    public $missingGameTeams = array();
}
class TeamsUtilLinkORM
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
     * Link game_team vis group slots
     */
    protected function linkGameTeam($team,$groupTypeName)
    {
        $groupParts = explode(':',$groupTypeName);
        $groupType = $groupParts[0];
        $groupName = $groupParts[1];
        $groupSlot = $groupParts[2];
        
        $gameTeams = $this->gameRepo->findAllGameTeamsByGroupSlot(
            $team->getProjectKey(),
            $team->getLevelKey(),
            $groupType,
            $groupName,
            $groupSlot
        );
        if (count($gameTeams) < 1)
        {
            $this->results->missingGameTeams[] = $team;
            return;
        }
        foreach($gameTeams as $gameTeam)
        {
            $gameTeam->setTeam($team);
        }
    }
    /* =============================================
     * TODO: Implement delete with negative number
     */
    protected function linkTeam($teamx)
    {   
        $results = $this->results;
        
        $num        = $teamx['num'];
        $levelKey   = $teamx['levelKey'];
        $projectKey = $teamx['projectKey'];
        
        $team = $this->teamRepo->findOneByProjectLevelNum($projectKey,$levelKey,$num);
        
        if (!$team)
        {
            // Really should not happen
            $results->missingTeams[] = $team;
            return;
        }
        // Process each group slot
        foreach($teamx['slots'] as $groupTypeName)
        {
            $this->linkGameTeam($team,$groupTypeName);
        }
    }
    /* ==============================================================
     * Main entry point
     */
    public function link($teams,$commit = false)
    {
        $this->results = $results = new TeamsUtilLinkORMResults();
        
        $results->commit = $commit;
        $results->total = count($teams);
        
        foreach($teams as $team)
        {
            $this->linkTeam($team);
        }
        if ($results->commit) 
        {
            $this->teamRepo->commit();
            $this->gameRepo->commit();
        }
        return $results;
    }
}
?>
