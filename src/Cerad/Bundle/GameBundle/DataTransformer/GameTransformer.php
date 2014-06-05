<?php
namespace Cerad\Bundle\GameBundle\DataTransformer;

// Used by twig and by exporter/importers

class GameTransformer
{
    protected $levelRepo;
    
    public function __construct($levelRepo)
    {
        $this->levelRepo = $levelRepo;
    }
    public function gameLevel($game)
    {
        $type  = $game->getGroupType(); // PP, QF etc
        $group = $game->getGroupKey();  // U12G Core B,  U12G Core QF1
        
        $groupParts = explode(' ',$group);
        
        $groupName = isset($groupParts[2]) ? $groupParts[2]: null; // A or QF1
        
        if ($type == 'PP') $groupName = 'PP'; // 'PP ' . $groupName;
        
        $level = $game->getLevelKey();
        $levelParts = explode('_',$level);
        
        return sprintf('%s %s %s',$levelParts[2],$levelParts[1],$groupName);
    }
    public function gameTeamGroup($team)
    {
        $type  = $team->getGame()->getGroupType(); // PP, QF etc
        $group = $team->getGame()->getGroupKey();  // U12G Core B,  U12G Core QF1
        $slot  = $team->getGroupSlot();            // U12G Core B1, U12G Core A 1st

        switch($type)
        {
            case 'VIP': return 'VIP';
            case 'PP':
                $slotParts = explode(' ',$slot);
                return $slotParts[2];
                return 'PP ' . $slotParts[2];
        }
        // Bit fragile but okay for now
        $groupParts = explode(' ',$group);
        $slotParts  = explode(' ',$slot);
        return sprintf('%s %s',                  $slotParts[2],$slotParts[3]);
        return sprintf('%s %s %s',$groupParts[2],$slotParts[2],$slotParts[3]);
    }
    // Core VIP => AYSO_VIP_Core
    public function extractLevel($div)
    {
        $levelKey = $this->extractLevelKey($div);
        return $this->levelRepo->find($levelKey);
    }
    public function extractLevelKey($div)
    {
        $divParts = explode(' ',$div);
        $levelKey = sprintf('AYSO_%s_%s',$divParts[1],$divParts[0]);
        return $levelKey;
    }
    public function extractGroupType($div)
    {
        $divParts = explode(' ',$div);
        
        if (count($divParts) < 3)
        {
            return $divParts[1]; // VIP
        }
        return substr($divParts[2],0,2);
    }
    // Core U12B PP  D1
    public function extractGroupKey($div,$slot)
    {   
        $divParts = explode(' ',$div);

        if (count($divParts) < 3)
        {
            return sprintf('%s %s',$divParts[1],$divParts[0]); // VIP
        }
        if ($divParts[2] == 'PP')
        {
            return sprintf('%s %s %s',$divParts[1],$divParts[0],substr($slot,0,1));
        }
        return sprintf('%s %s %s',$divParts[1],$divParts[0],$divParts[2]);
    }
    public function extractGroupSlot($div,$slot)
    {   
        $divParts = explode(' ',$div);

        if (count($divParts) < 3)
        {
            return sprintf('%s %s',$divParts[1],$divParts[0]); // VIP
        }
        return sprintf('%s %s %s',$divParts[1],$divParts[0],$slot);
    }
}
?>
