<?php

namespace Cerad\Bundle\GameBundle\Service\GameSchedule\Convert;

use Cerad\Bundle\CoreBundle\Excel\Loader as BaseLoader;

class ConvertTeamsRickToYaml extends BaseLoader
{
    protected $record = array
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
        $num = (int)$item['num'];
        if (!$num) return;
        
        $team = array();
        $team['num']        = $num;
        $team['name']       = $item['name'];
        $team['points']     = (int)$item['points'];
        $team['levelKey']   = $item['levelKey'];
        $team['groupSlot']  = $item['groupSlot'];
        $team['projectKey'] = $this->projectKey;
        
        $this->items[] = $team;
        
        return;
    }
}
?>
