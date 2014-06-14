<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Reader;

use Cerad\Bundle\CoreBundle\Excel\ExcelReader;

class TeamsReaderAll extends ExcelReader
{
    protected $projectKey;
    
    protected $record = array
    (
        'U10SAR' => array('cols' => 'U10', 'req' => true),
        'U10Key' => array('cols' => 'U10', 'req' => true, 'plus' => 1),
        
        'U12SAR' => array('cols' => 'U12', 'req' => true),
        'U12Key' => array('cols' => 'U12', 'req' => true, 'plus' => 1),
        
        'U14SAR' => array('cols' => 'U14', 'req' => true),
        'U14Key' => array('cols' => 'U14', 'req' => true, 'plus' => 1),
        
        'U16SAR' => array('cols' => 'U16', 'req' => true),
        'U16Key' => array('cols' => 'U16', 'req' => true, 'plus' => 1),
        
        'U19SAR' => array('cols' => 'U19', 'req' => true),
        'U19Key' => array('cols' => 'U19', 'req' => true, 'plus' => 1),
        
    );
    protected function processTeam($teamKey,$sar)
    {
        if (!$teamKey) return;
        
        $teamKeyParts = explode('-',$teamKey); // 12B-24
        $div = $teamKeyParts[0];
        $num = $teamKeyParts[1];
        
        $age    = 'U' . substr($div,0,2);
        $gender =       substr($div,2,1);
        
        $teamNum = (int)$num;
        $program = strpos($num,'x') ? 'Extra' : 'Core';
        
        $levelKey = sprintf('AYSO_%s%s_%s',$age,$gender,$program);
        
        $team = array(
            'teamKey'    => $teamKey,
            'teamNum'    => $teamNum,
            'levelKey'   => $levelKey,
            'sar'        => $sar,
            'projectKey' => $this->projectKey,
        );
        $this->items[] = $team;
    }
    protected function processItem($item)
    {
        foreach(array('U10','U12','U14','U16','U19') as $div) {
            $teamKey = $item[$div . 'Key'];
            $sar     = $item[$div . 'SAR'];
            $this->processTeam($teamKey,$sar);
        }
    }
    public function read($project,$filePath,$workSheetName = null)
    {
        $this->projectKey = $project->getKey();   
        
        return $this->load($filePath,$workSheetName);
    }
}
?>
