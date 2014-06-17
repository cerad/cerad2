<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Reader;

use Cerad\Bundle\CoreBundle\Excel\ExcelReader;

class TeamsReaderEayso extends ExcelReader
{
    protected $projectKey;
    
    // Team | Region # | Team Coach Last Name | Asst. Team Coach Last Name
    protected $record = array
    (
        'teamID'   => array('cols' => array('NG Team #','Team'), 'req' => true),
        'regionNum' => array('cols' => 'Region #',  'req' => true),
        
        'headCoachNameLast' => array('cols' => 'Team Coach Last Name',       'req' => true),
        'asstCoachNameLast' => array('cols' => 'Asst. Team Coach Last Name', 'req' => true),
    );
    protected function processItem($item)
    {
        $teamID = $item['teamID'];
        if (!$teamID) return;

        $regionNum = (int)$item['regionNum'];
        
        $coachName = isset($item['headCoachNameLast']) ? $item['headCoachNameLast'] : $item['asstCoachNameLast'];
        
        $teamIDParts = explode('-',$teamID);
        $div = $teamIDParts[0];
        $num = $teamIDParts[1];
        
        $age    = substr($div,1);
        $gender = substr($div,0,1);
        
        $teamNum = (int)$num;
        $program = stripos($num,'x') ? 'Extra' : 'Core';
        
        $levelKey = sprintf('AYSO_%s%s_%s',$age,$gender,$program);
        
        $team = array(
            'teamID'     => $teamID,
            'teamKey'    => sprintf('%s:%s:%02d',$this->projectKey,$levelKey,$teamNum),
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
