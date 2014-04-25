<?php

namespace Cerad\Bundle\GameBundle\Service\GameSchedule\Convert;

use Cerad\Bundle\CoreBundle\Excel\Loader as BaseLoader;

class ConvertRickToYaml extends BaseLoader
{
    protected $record = array
    (
        'num'   => array('cols' => 'Game #','req' => true),
        'date'  => array('cols' => 'Date',  'req' => true),
        'time1' => array('cols' => 'Start', 'req' => true),
        'time2' => array('cols' => 'Stop',  'req' => true),
        
        'venueName' => array('cols' => 'Site',  'req' => true),
        'fieldName' => array('cols' => 'Field', 'req' => true),
        'levelKey'  => array('cols' => 'Level', 'req' => true),
        'groupKey'  => array('cols' => 'Group', 'req' => true),
        'groupType' => array('cols' => 'GT',    'req' => true),

        'homeTeamName'  => array('cols' => 'Home Team', 'req' => true),
        'awayTeamName'  => array('cols' => 'Away Team', 'req' => true),
        
        'homeTeamGroupSlot'  => array('cols' => 'HT Group', 'req' => true),
        'awayTeamGroupSlot'  => array('cols' => 'AT Group', 'req' => true),
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
        
        $game = array();
        $game['projectKey'] = $this->projectKey;
        $game['num'] = $num;
        $game['type'] = 'Game';    
        
        $date  = $this->processDate($item['date']);
        $time1 = $this->processTime($item['time1']);
        $time2 = $this->processTime($item['time2']);
        
        $game['dtBeg'] = $date . ' ' . $time1;
        $game['dtEnd'] = $date . ' ' . $time2;
     
        $game['sportKey' ] = 'Soccer';
        $game['levelKey' ] = $item['levelKey'];
        $game['groupKey' ] = $item['groupKey'];
        $game['groupType'] = $item['groupType'];
        
        $game['venueName'] = $item['venueName'];
        $game['fieldName'] = $item['fieldName'];
        
        $game['homeTeamName'] = $item['homeTeamName'];
        $game['awayTeamName'] = $item['awayTeamName'];
        
        $game['homeTeamGroupSlot'] = $item['homeTeamGroupSlot'];
        $game['awayTeamGroupSlot'] = $item['awayTeamGroupSlot'];
        
        $game['officials'] = array(
            'Referee' => null,
            'AR1'     => null,
            'AR2'     => null,
        );
        $this->items[] = $game;
        return;
    }
}
?>
