<?php
namespace Cerad\Bundle\TournAdminBundle\Schedule\Games;

use Cerad\Component\Excel\Import as BaseImport;

class ScheduleGamesImportResults
{
    
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
        
    }
    /* ===============================================
     * Processes each item
     */
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        
        $game = $this->gameRepo->findOneByProjectNum($this->projectId,$num);
        
        if (!$game) return;
        
        $this->results->totalGameCount++;
        $gameModified = false;
        
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
            if (!$gameModified) 
            {
                $this->results->modifiedGameCount++;
                $gameModified = true;
            }
            $this->results->modifiedDateTimeCount++;
            
            $dtEnd = new \DateTime($date . ' ' . $stop);
            $game->setDtEnd($dtEnd);
        }
        
        // Fields
        $fieldName = $item['field'];
        if ($fieldName != $game->getField()->getName())
        {
            $gameField = $this->gameFieldRepo->findOneByProjectName($this->projectId,$fieldName);
            if (!$gameField)
            {
                // TODO: Allow creating new field here?
                /*
                $gameField = $gameFieldRepo->createGameField();
                $gameField->setSort     ($fieldSort);
                $gameField->setName     ($fieldName);
                $gameField->setVenue    ($fieldVenue);
                $gameField->setProjectId($projectId);
                $gameFieldRepo->save($gameField);
                
                // If we didn't commit then need local cache nonsense
                $gameFieldRepo->commit();*/
            }
            else
            {
                $game->setField($gameField);
                if (!$gameModified) 
                {
                    $this->results->modifiedGameCount++;
                    $gameModified = true;
                }
                $this->results->modifiedFieldCount++;
            }
        }
        
        // TODO: Handle venue changes as well
        
        /* ========================================================
         * TODO: Need to think about changing levels etc for a game
         */
        
        /* ========================================================
         * The scheduler needs to update both names and groups
         * They also need to update the game level
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
            
            if (!$gameModified) 
            {
                $this->results->modifiedGameCount++;
                $gameModified = true;
            }
            $this->results->modifiedHomeTeamCount++;
            
        }
        if (($awayTeamName != $awayTeam->getName()) || ($awayTeamGroup != $awayTeam->getGroup()))
        {
            $awayTeam->setName ($awayTeamName);
            $awayTeam->setGroup($awayTeamGroup);
            
            if (!$gameModified) 
            {
                $this->results->modifiedGameCount++;
                $gameModified = true;
            }
            $this->results->modifiedAwayTeamCount++;
            
        }
        return;
        
    }
    /* ==============================================================
     * Almost like the load but with a few tweaks
     * Main entry point
     * Returns a Results object
     */
    public function import($params)
    {
        $this->project   = $project = $params['project'];
        $this->projectId = $project->getId();
        
        $ss = $this->reader->load($params['filepath']);

      //if ($worksheetName) $ws = $reader->getSheetByName($worksheetName);
        $ws = $ss->getSheet(0);
        
        $rows = $ws->toArray();
        
        $header = array_shift($rows);
        
        $this->processHeaderRow($header);
        
        $this->results = new ScheduleGamesImportResults();
        $this->results->basename = $params['basename'];
        $this->results->totalGameCount        = 0;
        $this->results->modifiedGameCount     = 0;
        $this->results->modifiedFieldCount    = 0;
        $this->results->modifiedDateTimeCount = 0;
        $this->results->modifiedHomeTeamCount = 0;
        $this->results->modifiedAwayTeamCount = 0;
        
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
