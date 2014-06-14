<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Reader;

use Cerad\Bundle\CoreBundle\Excel\ExcelReader;

class TeamsReaderEayso extends ExcelReader
{
    protected $projectKey;
    
    // Team | Region # | Team Coach Last Name | Asst. Team Coach Last Name
    protected $record = array
    (
        'teamKey'   => array('cols' => 'Team',     'req' => true),
        'regionNum' => array('cols' => 'Region #', 'req' => true),
        
        'headCoachNameLast' => array('cols' => 'Team Coach Last Name',       'req' => true),
        'asstCoachNameLast' => array('cols' => 'Asst. Team Coach Last Name', 'req' => true),
    );
    protected function processItem($item)
    {
        print_r($item); die();
        $teamKey = $item['teamKey'];
        if (!$teamKey) return;

        $team = array();
        $team['projectKey'] = $this->projectKey;
        $team['num']      = $num;
        $team['role']     = 'Physical';
        $team['status']   = 'Active';
        $team['sportKey'] = 'Soccer';
        
        $team['levelKey'] = $item['levelKey'];
        $team['region']   = $item['region'];
        $team['name']     = $item['name'];
        $team['points']   = $item['points'];
        
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
