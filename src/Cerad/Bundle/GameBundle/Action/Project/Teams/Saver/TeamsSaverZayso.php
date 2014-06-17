<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Saver;

use Cerad\Bundle\CoreBundle\Event\Team\ChangedTeamEvent;

class TeamsSaverZaysoResults
{
    public $commit = false;
    
    public $basename;
    
    public $total    = 0;
    public $created  = 0;
    public $updated  = 0;
    public $deleted  = 0;
}
class TeamsSaverZayso
{
    protected $results;
    
    protected $teamRepo;
        
    public function __construct($teamRepo)
    {
        $this->teamRepo = $teamRepo;
    }
    public function setDispatcher($dispatcher) { $this->dispatcher = $dispatcher; }
    
    protected function dispatch($team,$groupSlot = null)
    {
        $event = new ChangedTeamEvent($team,$groupSlot);
        $this->dispatcher->dispatch(ChangedTeamEvent::Changed,$event);
    }
    /* =============================================
     * TODO: Implement delete with negative number
     */
    protected function saveTeam($teamx)
    {   $item = $teamx;
        $results = $this->results;
        
        $key        = $item['key'];
        $num        = $item['num'];
        $levelKey   = $item['levelKey'];
        $projectKey = $item['projectKey'];
        
        $team = $this->teamRepo->findOneByProjectLevelNum($projectKey,$levelKey,$num);
        
        if (!$team)
        {
            $team = $this->teamRepo->createTeam();
            $team->setKey       ($key);
            $team->setNum       ($num);
            $team->setLevelKey  ($levelKey);
            $team->setProjectKey($projectKey);
            
            $team->setName  ($teamx['name']);
            $team->setOrgKey($teamx['region']);
            $team->setPoints($teamx['points']);
            
            $results->created++;
            $this->teamRepo->persist($team);
            $this->dispatch($team);
            return $team;
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
        if ($changed) $this->dispatch($team);
        return $team;
    }
    /* ==============================================================
     * The syncer just sends an event out for each slot
     */
    protected function syncTeam($item,$team)
    {
        foreach($item['slots'] as $slot)
        {
            $this->dispatch($team,$slot);
        }
    }
    /* ==============================================================
     * Main entry point
     */
    public function save($teams,$commit = false)
    {
        $this->results = $results = new TeamsSaverZaysoResults();
        
        $results->commit = $commit;
        $results->total = count($teams);
        
        foreach($teams as $teamx)
        {
            $team = $this->saveTeam($teamx);
            
            $this->syncTeam($teamx,$team);
        }
         
        if ($results->commit) $this->teamRepo->commit();
        
        return $results;
    }
}
?>
