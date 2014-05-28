<?php

namespace Cerad\Bundle\GameBundle\Service\GameSchedule\Convert;

use Cerad\Bundle\CoreBundle\Excel\Loader as BaseLoader;

class ConvertTeamsRickToYaml extends BaseLoader
{
    protected $record = array
    (
        'teamNum'   => array('cols' => 'TEAM NO.',  'req' => true),
        'region'    => array('cols' => 'REGION',    'req' => true),
        'pool'      => array('cols' => 'Pool',      'req' => true),
        'poolFri'   => array('cols' => 'Fri Pool',  'req' => true),
        'poolThu'   => array('cols' => 'Thurs Pool','req' => true),
        'points'    => array('cols' => 'Points',    'req' => true),
    );
    protected $recordx = array
    (
        'num'       => array('cols' => 'Team #', 'req' => true),
        'name'      => array('cols' => 'Name',   'req' => true),
        'levelKey'  => array('cols' => 'Level',  'req' => true),
        'points'    => array('cols' => 'Points', 'req' => true),
        
        'groupSlot' => array('cols' => 'Group Slot', 'req' => true),
    );
    protected $projectKey = null;
    public function setProjectKey($projectKey)
    {
        $this->projectKey = $projectKey;
    }
    protected function processItem($item)
    {
        // Extraxt the team number 19G-08
        $teamNum = $item['teamNum'];
        if (!$teamNum) return;
        $teamNumParts = explode('-',$teamNum);
        if (count($teamNumParts) != 2) { print_r($item); die(); }
        $num = (int)$teamNumParts[1];
        
        // Extract keys U19G Core D5
        $groupSlot = $item['poolThu'] ? $item['poolThu'] : $item['pool'];
        $groupSlotParts = explode(' ',$groupSlot);
        if (count($groupSlotParts) != 3) { print_r($item); die(); }
        
        $levelKey = sprintf('AYSO_%s_%s',$groupSlotParts[0],$groupSlotParts[1]);
        
        // Points
        $points = $item['points'] ? (int)$item['points'] : 0;
        
        // Create team
        $team = array();
        $team['num']        = $num;
        $team['name']       = $item['region'];
        $team['points']     = $points;
        $team['levelKey']   = $levelKey;
        $team['groupSlot']  = $groupSlot;
        $team['projectKey'] = $this->projectKey;
        
        $this->items[] = $team;
        
        return;
    }
}
?>
