<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Import;

use Cerad\Component\Excel\Loader as BaseLoader;

class ScheduleGameImportReadXLS extends BaseLoader
{
    protected $levelRepo;
    protected $projectKey;
    protected $gameTransformer;
    protected $gameSlotDurations;
    
    protected $record = array
    (
        'num'  => array('cols' => 'Game','req' => true),
        'date' => array('cols' => 'Date','req' => true),
        'time' => array('cols' => 'Time','req' => true),
        
        'venueName' => array('cols' => 'Venue',   'req' => true),
        'fieldName' => array('cols' => 'Field',   'req' => true),
        'division'  => array('cols' => 'Division','req' => true),

        'homeTeamName'  => array('cols' => 'Home Team Name', 'req' => true),
        'awayTeamName'  => array('cols' => 'Away Team Name', 'req' => true),
        
        'homeTeamGroupSlot' => array('cols' => 'Home Team Group', 'req' => true),
        'awayTeamGroupSlot' => array('cols' => 'Away Team Group', 'req' => true),
    );
    public function __construct($gameTransformer,$gameSlotDurations,$levelRepo)
    {
        parent::__construct();
        
        $this->levelRepo = $levelRepo;
        $this->gameTransformer = $gameTransformer;
        $this->gameSlotDurations = $gameSlotDurations;
    }
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        if (!$num) return;

        $game = array();
        $game['projectKey'] = $this->projectKey;
        $game['num']  = $num;
        $game['type'] = 'Game';
        $game['sportKey' ] = 'Soccer';
        
        $div = $item['division'];
        $level = $this->gameTransformer->extractLevel($div);
       
        $date = $this->processDate($item['date']);
        $time = $this->processTime($item['time']);
        
        $dtStr = sprintf('%s %s',$date,$time);
        $dtBeg = new \DateTime($dtStr);
        $dtEnd = clone($dtBeg);
        $dtEnd->add(new \DateInterval(sprintf('PT%dM',$this->gameSlotDurations[$level->getAge()])));
        
        $game['dtBeg'] = $dtBeg->format('Y-m-d H:i:s');
        $game['dtEnd'] = $dtEnd->format('Y-m-d H:i:s');
        
        $game['division'] = $item['division'];
        
        $game['levelKey' ] = $level->getKey();
        $game['groupKey' ] = $this->gameTransformer->extractGroupKey ($div,$item['homeTeamGroupSlot']);
        $game['groupType'] = $this->gameTransformer->extractGroupType($div);
        
        $game['venueName'] = $item['venueName'];
        $game['fieldName'] = $item['fieldName'];
        
        $game['homeTeamName'] = $item['homeTeamName'];
        $game['awayTeamName'] = $item['awayTeamName'];
        
        $game['homeTeamGroupSlot'] = $this->gameTransformer->extractGroupSlot($div,$item['homeTeamGroupSlot']);
        $game['awayTeamGroupSlot'] = $this->gameTransformer->extractGroupSlot($div,$item['awayTeamGroupSlot']);
        
        $this->items[] = $game;
        
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
