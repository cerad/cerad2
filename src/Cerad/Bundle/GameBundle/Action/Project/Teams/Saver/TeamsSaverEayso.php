<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Saver;

use Cerad\Bundle\CoreBundle\Event\Team\ChangedTeamEvent;

class TeamsSaverEaysoResults
{
    public $commit = false;
    
    public $basename;
    
    public $total    = 0;
    public $updated  = 0;
    public $missingTeam = 0;
    public $missingRegion = 0;
    public $missingCoachName  = 0;
    public $regionMismatch = 0;
}
class TeamsSaverEayso
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
    /* ===============================================
     * teamKey:    BU10-01
     * teamNum:    1
     * levelKey:   AYSO_U10B_Core
     * regionNum:  68
     * coachName:  Caron
     * projectKey: AYSONationalGames2014
     */
    protected function saveTeam($item)
    {   
        $results = $this->results;
        $missing = false;
        
        $teamNum    = (int)$item['teamNum'];
        $regionNum  = (int)$item['regionNum'];
        $coachName  =      $item['coachName'];
        
        $levelKey   =      $item['levelKey'];
        $projectKey =      $item['projectKey'];
        
        // No coach, not much point
        if (!$coachName)
        {
            $results->missingCoachName++;
            $missing = true;
        }
        $coachNamex = ucfirst($coachName);
        
        // No region, not much point
        if (!$regionNum)
        {
            $results->missingRegion++;
            $missing = true;
        }
        
        // Need a team
        $team = $this->teamRepo->findOneByProjectLevelNum($projectKey,$levelKey,$teamNum);
        if (!$team) 
        {
            $results->missingTeam++;
            $missing = true;
        }
        if ($missing) return;
        
        // Make sure regions match 
        $orgKeyParts = explode('-',$team->getOrgKey());
        if (count($orgKeyParts) != 3)
        {
           $results->regionMismatch++;  // No sar in team?
           return; 
        }
        // TODO: Handle region num = AREA
        $teamRegionNum = (int)$orgKeyParts[2];
        if ($teamRegionNum != $regionNum)
        {
          //print_r($item); print_r($orgKeyParts); die();
            $results->regionMismatch++;  // Lots of issues in the eqyso report
            return; 
        }
        $teamNameOriginal = $team->getName();
        
        $teamNameParts = explode(' ',$teamNameOriginal);
    
        // Don't overwrite existing names.
        if (count($teamNameParts) > 2) return;
        
        $teamNameParts[2] = $coachNamex;
        
        $teamNameNew = sprintf('%s %s %s',$teamNameParts[0],$teamNameParts[1],$teamNameParts[2]);
        
        if ($teamNameOriginal == $teamNameNew) return;
  
        $team->setName($teamNameNew);
        $this->dispatch($team);
        
        $results->updated++;
      //print_r($item);
    }
    /* ==============================================================
     * Main entry point
     */
    public function save($teams,$commit = false)
    {
        $this->results = $results = new TeamsSaverEaysoResults();
        
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
