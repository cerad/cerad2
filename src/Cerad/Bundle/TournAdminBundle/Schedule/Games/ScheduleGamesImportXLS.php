<?php
namespace Cerad\Bundle\TournAdminBundle\Schedule\Games;

use Cerad\Component\Excel\Loader as BaseLoader;

class ImportResults
{
    
}
class ScheduleGamesImportXLS extends BaseLoader
{
    protected $record = array
    (
        'num'   => array('cols' => 'Game',    'req' => true),
        'date'  => array('cols' => 'Referee', 'req' => true),
        'time'  => array('cols' => 'Referee', 'req' => true, 'plus' => 1),
        'venue' => array('cols' => 'Referee', 'req' => true, 'plus' => 2),
        'field' => array('cols' => 'Referee', 'req' => true, 'plus' => 2),
    );
    public function __construct($gameRepo)
    {
        parent::__construct();
        
        $this->gameRepo   = $gameRepo;
    }
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        
        $game = $this->gameRepo->findOneByProjectNum($this->projectId,$num);
        
        if (!$game) return;
        
        $this->results->totalGameCount++;
        
        return;
        
        $names = array(
            1 => $item['referee'],
            2 => $item['ar1'],
            3 => $item['ar2'],
        );
        
        for($slot = 1; $slot < 4; $slot++)
        {
            $official = $game->getOfficialForSlot($slot);
            $officialName = $official->getPersonNameFull();
            if ($officialName != $names[$slot])
            {
                $official->setPersonNameFull($names[$slot]);
                $this->results->modifiedSlotCount++;
            }
        }
    }
    /* ==============================================================
     * Almost like the load but with a few tewaks
     */
    public function import($params)
    {
        $project = $params['project'];
        $this->projectId = $project->getId();
        
        $reader = $this->excel->load($params['filepath']);

      //if ($worksheetName) $ws = $reader->getSheetByName($worksheetName);
        $ws = $reader->getSheet(0);
        
        $rows = $ws->toArray();
        
        $header = array_shift($rows);
        
        $this->processHeaderRow($header);
        
        $this->results = new ImportResults();
        $this->results->basename = $params['basename'];
        $this->results->totalGameCount    = 0;
        $this->results->modifiedGameCount = 0;
        
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
