<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Saver;

class TeamsSaverAllResults
{
    public $commit = false;
    
    public $basename;
    
    public $total    = 0;
    public $created  = 0;
    public $updated  = 0;
    public $deleted  = 0;
}
class TeamsSaverAll
{
    protected $results;
    
    protected $teamRepo;
        
    public function __construct($teamRepo)
    {
        $this->teamRepo = $teamRepo;
    }
    /* =============================================
     * TODO: Implement delete with negative number
     */
    protected function transformSar($sar)
    {
        if (!$sar) return $sar;
        $sarParts = explode('-',$sar);
        $section  = (int)$sarParts[0];
        $area     =      $sarParts[1];
        $region   = (int)$sarParts[2];
        $fmt = $region ? '%02d-%s-%04u' : '%02d-%s-%s';
        
        return sprintf($fmt,$section,$area,$region);
        
    }
    protected function saveTeam($item)
    {   
        $results = $this->results;
        
        $num        = $item['teamNum'];
        $sar        = $item['sar'];
        $levelKey   = $item['levelKey'];
        $projectKey = $item['projectKey'];
        
        $orgKey = $this->transformSar($sar);
        
        $name = trim(sprintf('#%02u %s',$num,$orgKey));
        
        $team = $this->teamRepo->findOneByProjectLevelNum($projectKey,$levelKey,$num);
        
        if (!$team)
        {
            $team = $this->teamRepo->createTeam();
            $team->setNum       ($num);
            $team->setLevelKey  ($levelKey);
            $team->setProjectKey($projectKey);
            
            $team->setName  ($name);
            $team->setOrgKey($sar);
            
            $results->created++;
            $this->teamRepo->persist($team);
            return;
        }
        $changed = false;

        if ($name != $team->getName())
        {
            $team->setName($name);
            if (!$changed) $results->updated++;
            $changed = true;
        }
        if ($sar != $team->getOrgKey())
        {
            $team->setOrgKey($sar);
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
        $this->results = $results = new TeamsSaverAllResults();
        
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
