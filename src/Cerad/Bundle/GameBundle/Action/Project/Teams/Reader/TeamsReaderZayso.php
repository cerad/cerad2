<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Reader;

use Cerad\Bundle\CoreBundle\Excel\ExcelReader;

class TeamsReaderZayso extends ExcelReader
{
    protected $projectKey;
    
    protected $record = array
    (
        'num'  => array('cols' => 'Team','req' => true),
        
        'levelKey' => array('cols' => 'Level',   'req' => true),
        
        'region' => array('cols' => 'Region', 'req' => true),
        'name'   => array('cols' => 'Name',   'req' => true),
        'points' => array('cols' => 'SfP',    'req' => true),

        'slot1' => array('cols' => 'Slots', 'req' => true),
        'slot2' => array('cols' => 'Slots', 'req' => true, 'plus' => 1),
        'slot3' => array('cols' => 'Slots', 'req' => true, 'plus' => 2),
        'slot4' => array('cols' => 'Slots', 'req' => true, 'plus' => 3),
        'slot5' => array('cols' => 'Slots', 'req' => true, 'plus' => 4),
    );
    protected function transformName($name,$levelKey)
    {
        $nameParts = explode(' ',$name);
        $num = $nameParts[0];
        if (strlen($num) != 3) return $name;
        
        $level = strpos($levelKey,'Core') ? 'c' : 'e';
        $num = $num . $level;
        
        if (count($nameParts) == 1) return $num;
        
        return $num . substr($name,3);
    }
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        if (!$num) return;

        $levelKey   = $item['levelKey'];
        $projectKey = $this->projectKey;
        
        // Little hack to add level to team name prefix
        $name = $this->transformName($item['name'],$levelKey);
        
        $team = array();
        $team['key']      = sprintf('%s:%s:%02d',$projectKey,$item['levelKey'],$num);
        $team['num']      = $num;
        $team['role']     = 'Physical';
        $team['status']   = 'Active';
        $team['sportKey'] = 'Soccer';
        
        $team['region']     = $item['region'];
        $team['name']       = $name;
        $team['points']     = $item['points'];
        $team['levelKey']   = $levelKey;
        $team['projectKey'] = $projectKey;
        
        $slots = array();
        for($i = 1; $i < 6; $i++)
        {
            $itemSlotKey = 'slot' . $i;
            if ($item[$itemSlotKey]) $slots[] = $item[$itemSlotKey];
        }
        $team['slots'] = $slots;
                
        $this->items[] = $team;
        
        return;
        
    }
    /* ==============================================================
     * Almost like the load but with a few tewaks
     */
    public function read($project,$filePath,$workSheetName = null)
    {
        $this->projectKey = $project->getKey();   
        
        return $this->load($filePath,$workSheetName);
    }
}
?>
