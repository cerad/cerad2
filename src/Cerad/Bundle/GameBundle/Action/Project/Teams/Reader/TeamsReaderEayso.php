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
        $teamKey = $item['teamKey'];
        if (!$teamKey) return;

        $regionNum = (int)$item['regionNum'];
        
        $coachName = isset($item['headCoachNameLast']) ? $item['headCoachNameLast'] : $item['asstCoachNameLast'];
        
        $teamKeyParts = explode('-',$teamKey);
        $div = $teamKeyParts[0];
        $num = $teamKeyParts[1];
        
        $age    = substr($div,1);
        $gender = substr($div,0,1);
        
        $teamNum = (int)$num;
        $program = strpos($num,'x') ? 'Extra' : 'Core';
        
        $levelKey = sprintf('AYSO_%s%s_%s',$age,$gender,$program);
        
        $team = array(
            'teamKey'    => $teamKey,
            'teamNum'    => $teamNum,
            'levelKey'   => $levelKey,
            'regionNum'  => $regionNum,
            'coachName'  => $coachName,
            'projectKey' => $this->projectKey,
        );
        $this->items[] = $team;
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
