<?php
namespace Cerad\Bundle\TournAdminBundle\Schedule\Games;

use Cerad\Component\Excel\Import as BaseImport;

class ScheduleGamesImportResults
{
    public $basename;
    public $filepath;
    
    public $totalGameCount        = 0;
    public $modifiedGameCount     = 0;
    public $modifiedFieldCount    = 0;
    public $modifiedVenueCount    = 0;
    public $modifiedDateTimeCount = 0;
    public $modifiedHomeTeamCount = 0;
    public $modifiedAwayTeamCount = 0;
    
    protected $gameId = null;
    public function modifiedGameCountInc($game)
    {
        if ($game->getId() != $this->gameId)
        {
            $this->gameId = $game->getId();
            $this->modifiedGameCount++;
        }
    }
}
class ScheduleGamesImportXLS extends BaseImport
{
    protected $project;
    protected $projectId;
    
    protected $gameRepo;
    protected $gameFieldRepo;
    
    protected $record = array
    (
        'num'      => array('cols' => 'Game',      'req' => true),
        'date'     => array('cols' => 'Date',      'req' => true),
        'start'    => array('cols' => 'Start',     'req' => true),
        'stop'     => array('cols' => 'Stop',      'req' => true),
        
        'venue'    => array('cols' => 'Venue',     'req' => true),
        'field'    => array('cols' => 'Field',     'req' => true),
        
        'level'    => array('cols' => 'Level',     'req' => true),
        'group'    => array('cols' => 'Group',     'req' => true),
        'gt'       => array('cols' => 'GT',        'req' => true), // Group Type
        
        'homeTeam' => array('cols' => 'Home Team', 'req' => true),
        'awayTeam' => array('cols' => 'Away Team', 'req' => true),
        
        'homeTeamGroup' => array('cols' => 'HT Group', 'req' => true),
        'awayTeamGroup' => array('cols' => 'AT Group', 'req' => true),
    );
    public function __construct($gameRepo,$gameFieldRepo)
    {
        parent::__construct();
        
        $this->gameRepo      = $gameRepo;
        $this->gameFieldRepo = $gameFieldRepo;
        
        $this->results = new ScheduleGamesImportResults();
    }
    /* ===============================================
     * Processes each item
     */
    protected function processItem($item)
    {
        $results       = $this->results;
        $gameRepo      = $this->gameRepo;
        $gameFieldRepo = $this->gameFieldRepo;
        
        $num = (int)$item['num'];
        
        $game = $gameRepo->findOneByProjectNum($this->projectId,$num);
        
        if (!$game) return;
        
        $results->totalGameCount++;
        
        // TODO: Allow deleting game with negative number
        
        // Handle start/stop
        $date  = $this->processDate($item['date']);
        $start = $this->processTime($item['start']);
        $stop  = $this->processTime($item['stop']);
        
        $dtBeg = new \DateTime($date . ' ' . $start);
        
        // Compare values
        if ($dtBeg != $game->getDtBeg())
        {
            $game->setDtBeg($dtBeg);
            
            $results->modifiedGameCountInc($game);
            $results->modifiedDateTimeCount++;
            
            $dtEnd = new \DateTime($date . ' ' . $stop);
            $game->setDtEnd($dtEnd);
        }
        
        // Fields
        $fieldName = $item['field'];
        $venueName = $item['venue'];
        if ($fieldName != $game->getField()->getName())
        {
            $gameField = $gameFieldRepo->findOneByProjectName($this->projectId,$fieldName);
            if (!$gameField)
            {
                // TODO: Allow creating new field here?
                $gameField = $gameFieldRepo->createGameField();
              //$gameField->setSort     ($fieldSort);
                $gameField->setName     ($fieldName);
                $gameField->setVenue    ($venueName);
                $gameField->setProjectId($this->projectId);
                $gameFieldRepo->save($gameField);
                
                // If we didn't commit then need local cache nonsense
                $gameFieldRepo->commit();
            }
            $game->setField($gameField);
            $results->modifiedGameCountInc($game);
            $results->modifiedFieldCount++;
        }
        // TODO: Handle venue changes as well
        if ($venueName != $game->getField()->getVenue())
        {
            $game->getField()->setVenue($venueName);
            $results->modifiedGameCountInc($game);
            $results->modifiedVenueCount++;
        }
        /* ========================================================
         * TODO: Need to think about changing levels etc for a game
         */
        $level = $item['level'];
        $group = $item['group'];
        $gt    = $item['gt'];
        
        if ($level != $game->getLevelId())
        {
            $game->setLevelId($level);
            $results->modifiedGameCountInc($game);
        }
        if ($group != $game->getGroup())
        {
            $game->setGroup($group);
            $results->modifiedGameCountInc($game);
        }
        if ($gt != $game->getGroupType())
        {
            $game->setGroupType($gt);
            $results->modifiedGameCountInc($game);
        }
        /* ========================================================
         * The scheduler needs to update both names and groups
         * They also need to update the game level and group
         */
        $homeTeamName = $item['homeTeam'];
        $awayTeamName = $item['awayTeam'];
        
        $homeTeamGroup = $item['homeTeamGroup'];
        $awayTeamGroup = $item['awayTeamGroup'];
        
        $homeTeam = $game->getHomeTeam();
        $awayTeam = $game->getAwayTeam();
        
        if (($homeTeamName != $homeTeam->getName()) || ($homeTeamGroup != $homeTeam->getGroup()))
        {
            $homeTeam->setName ($homeTeamName);
            $homeTeam->setGroup($homeTeamGroup);
            
            $results->modifiedGameCountInc($game);
            $results->modifiedHomeTeamCount++;
            
        }
        if (($awayTeamName != $awayTeam->getName()) || ($awayTeamGroup != $awayTeam->getGroup()))
        {
            $awayTeam->setName ($awayTeamName);
            $awayTeam->setGroup($awayTeamGroup);
            
            $results->modifiedGameCountInc($game);
            $results->modifiedAwayTeamCount++;
            
        }
    }
    /* ==============================================================
     * Almost like the load but with a few tweaks
     * Main entry point
     * Returns a Results object
     */
    public function import($params)
    {
        $this->results->basename = $params['basename'];
        $this->results->filepath = $params['filepath'];
        
        $this->project   = $project = $params['project'];
        $this->projectId = $project->getId();
        
        $ss = $this->reader->load($params['filepath']);

      //if ($worksheetName) $ws = $reader->getSheetByName($worksheetName);
        $ws = $ss->getSheet(0);
        
        $rows = $ws->toArray();
        
        $header = array_shift($rows);
        
        $this->processHeaderRow($header);
        
        // Insert each record
        foreach($rows as $row)
        {
            $item = $this->processDataRow($row);
            
            $this->processItem($item);
        }
        $this->gameRepo->commit();
        
        return $this->results;
    }
}
?>
