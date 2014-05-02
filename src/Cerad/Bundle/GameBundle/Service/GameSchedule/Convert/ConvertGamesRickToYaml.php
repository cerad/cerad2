<?php

namespace Cerad\Bundle\GameBundle\Service\GameSchedule\Convert;

use Cerad\Bundle\CoreBundle\Excel\Loader as BaseLoader;

class ConvertGamesRickToYaml extends BaseLoader
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
        if ($game['levelKey'] == 'AYSO_U19B_Core' || true)
        {
            $game = $this->transform($game);
        }
        $this->items[] = $game;
        return;
    }
    protected function transform($game)
    {
        $levelParts = explode('_',$game['levelKey']);
        $levelDiv     = $levelParts[1];
        $levelProgram = $levelParts[2];
        
        $groupParts = explode(' ',$game['groupKey']);
        switch(count($groupParts))
        {
            case 3: $groupPool = $groupParts[2]; break;
            case 2: 
                $groupPoolPart = $groupParts[1]; 
                $groupPool = isset($this->groupPoolTransform[$groupPoolPart]) ? 
                    $this->groupPoolTransform[$groupPoolPart] : 
                    $groupPoolPart;
                break;
            default:
                die('Group Key ' . $game['groupKey']);
        }
        $game['groupKey'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$groupPool);
        
        $game['groupType'] = isset($this->groupTypeTransform[$game['groupType']]) ? 
            $this->groupTypeTransform[$game['groupType']] : 
            $game['groupType'];
        
        if ($game['groupType'] == 'PP')
        {
            $homeParts = explode(' ',$game['homeTeamGroupSlot']);
            $game['homeTeamGroupSlot'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$homeParts[2]);
            
            $awayParts = explode(' ',$game['awayTeamGroupSlot']);
            $game['awayTeamGroupSlot'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$awayParts[2]);
        }
        if (isset($this->teamGroupSlotTransform[$game['homeTeamGroupSlot']]))
        {
            $slot = $this->teamGroupSlotTransform[$game['homeTeamGroupSlot']];
            $game['homeTeamGroupSlot'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$slot);
        }
        if (isset($this->teamGroupSlotTransform[$game['awayTeamGroupSlot']]))
        {
            $slot = $this->teamGroupSlotTransform[$game['awayTeamGroupSlot']];
            $game['awayTeamGroupSlot'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$slot);
        }
        return $game;
    }
    protected $groupTypeTransform = array
    (
        'PP' => 'PP',
        'QF1' => 'QF', 'QF2' => 'QF', 'QF3'  => 'QF', 'QF4'  => 'QF',
        'CH5' => 'SF', 'CH6' => 'SF', 'CB9'  => 'SF', 'CB10' => 'SF',
//      'CH7' => 'FM', 'CH8' => 'CM', 'CB11' => 'FM', 'CB12' => 'CM',
        'CH7' => 'FM', 'CH8' => 'FM', 'CB11' => 'FM', 'CB12' => 'FM',
    );
    protected $groupPoolTransform = array
    (
        'QF1' => 'QF 1', 'QF2' => 'QF 2', 'QF3' => 'QF 3', 'QF4'  => 'QF 4',
        'SF5' => 'SF 1', 'SF6' => 'SF 2', 'CB9' => 'SF 3', 'CB10' => 'SF 4',
        
        'FM7' => 'FM 1', 'CB11' => 'FM 3',
        'CM8' => 'FM 2', 'CB12' => 'FM 4',
    );
    protected $teamGroupSlotTransform = array
    (
        '1st in PP A' => 'A 1st', '2nd in PP A' => 'A 2nd',
        '1st in PP B' => 'B 1st', '2nd in PP B' => 'B 2nd',
        '1st in PP C' => 'C 1st', '2nd in PP C' => 'C 2nd',
        '1st in PP D' => 'D 1st', '2nd in PP D' => 'D 2nd',
        
        'Winner QF1' => 'QF 1 Winner', 'Runner-Up QF1' => 'QF 1 Runner Up',
        'Winner QF2' => 'QF 2 Winner', 'Runner-Up QF2' => 'QF 2 Runner Up',
        'Winner QF3' => 'QF 3 Winner', 'Runner-Up QF3' => 'QF 3 Runner Up',
        'Winner QF4' => 'QF 4 Winner', 'Runner-Up QF4' => 'QF 4 Runner Up',
        
        'Winner CH5' => 'SF 1 Winner', 'Runner-Up CH5' => 'SF 1 Runner Up',
        'Winner CH6' => 'SF 2 Winner', 'Runner-Up CH6' => 'SF 2 Runner Up',
        
        'Winner CB9'  => 'SF 3 Winner', 'Runner-Up CB9'  => 'SF 3 Runner Up',
        'Winner CB10' => 'SF 4 Winner', 'Runner-Up CB10' => 'SF 4 Runner Up',
    );
}
?>
