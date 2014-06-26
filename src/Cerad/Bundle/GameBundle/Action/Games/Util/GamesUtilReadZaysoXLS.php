<?php

namespace Cerad\Bundle\GameBundle\Action\Games\Util;

// 12 June 2014 - This was pointing to ols Component stuff
use Cerad\Bundle\CoreBundle\Excel\ExcelLoader;

class GamesUtilReadZaysoXLS extends ExcelLoader
{
    protected $levelRepo;
    protected $projectKey;
    protected $gameSlotDurations;
    
    protected $record = array
    (
        'project' => array('cols' => 'Project','req' => false),
        
        'num'  => array('cols' => 'Game','req' => true),
        'date' => array('cols' => 'Date','req' => true),
        'time' => array('cols' => 'Time','req' => true),
        
        'venueName' => array('cols' => 'Venue','req' => true),
        'fieldName' => array('cols' => 'Field','req' => true),
        'group'     => array('cols' => 'Group','req' => true),

        'homeTeamName'  => array('cols' => 'Home Team Name', 'req' => true),
        'awayTeamName'  => array('cols' => 'Away Team Name', 'req' => true),
        
        'homeTeamGroupSlot' => array('cols' => array('HT Slot','Home Team Group'), 'req' => true),
        'awayTeamGroupSlot' => array('cols' => array('AT Slot','Away Team Group'), 'req' => true),
        
        'Referee' => array('cols' => 'Referee', 'req' => false),
        'AR1'     => array('cols' => 'Referee', 'req' => false, 'plus' => 1),
        'AR2'     => array('cols' => 'Referee', 'req' => false, 'plus' => 2),
        
    );
    public function __construct($levelRepo,$gameSlotDurations)
    {
        parent::__construct();
        
        $this->levelRepo         = $levelRepo;
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
        
        $group = $item['group'];
        $groupParts = explode(':',$group);
        $levelKey  = $groupParts[0];
        $groupType = $groupParts[1];
        $groupName = $groupParts[2];
       
        $date = $this->processDate($item['date']);
        $time = $this->processTime($item['time']);
        
        $dtStr = sprintf('%s %s',$date,$time);
        $dtBeg = new \DateTime($dtStr);
        $dtEnd = clone($dtBeg);
        
        // TODO: This could be optional
        // TODO: This should come from project levels
        // TODO: Game duration could depend on group type
        // TODO: Support stop time options
        $level = $this->levelRepo->find($levelKey);
        $dtEnd->add(new \DateInterval(sprintf('PT%dM',$this->gameSlotDurations[$level->getAge()])));
        
        $game['dtBeg'] = $dtBeg->format('Y-m-d H:i:s');
        $game['dtEnd'] = $dtEnd->format('Y-m-d H:i:s');
        
        $game['venueName'] = $item['venueName'];
        $game['fieldName'] = $item['fieldName'];
        
        $game['group'] = $item['group'];
        
        $game['levelKey' ] = $levelKey;
        $game['groupType'] = $groupType;
        $game['groupName'] = $groupName;
        
        /* ==========================================
         * Teams
         */
        $teams = array();
        $teamSlot = 1;
        foreach(array('home','away') as $role)
        {
            $team = array();
            $team['slot'] = $teamSlot++;
            $team['role'] = ucfirst($role);
            
            $team['name']      = $item[$role . 'TeamName'];
            $team['groupSlot'] = $item[$role . 'TeamGroupSlot'];
            
            $teams[] = $team;
        }
        $game['gameTeams'] = $teams;
        
        // Officials if have them
        if (isset($item['Referee']))
        {
            $officials = array();
            $slot = 1;
            foreach(array('Referee','AR1','AR2') as $key)
            {
                $officials[] = array(
                    'slot' => $slot++, 
                    'role' => $key, 
                    'personNameFull' => $item[$key]);
            }
            $game['officials'] = $officials;
        }
        // Stash
        $this->items[] = $game;
        return;
        
    }
    /* ==============================================================
     * Almost like the load but with a few tewaks
     */
    public function read($filePath, $project, $workSheetName = null)
    {
        $this->projectKey = is_object($project) ? $project->getKey() : $project;   
        
        return $this->load($filePath,$workSheetName);
    }
}
?>
