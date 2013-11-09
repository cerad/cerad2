<?php
namespace Cerad\Bundle\TournAdminBundle\Schedule\Officials;

use Cerad\Component\Excel\Loader as BaseLoader;

class ImportResults
{
    
}
class ScheduleOfficialsImportXLS extends BaseLoader
{
    protected $record = array
    (
        'num'     => array('cols' => 'Game',    'req' => true),
        'referee' => array('cols' => 'Referee', 'req' => true),
        'ar1'     => array('cols' => 'Referee', 'req' => true, 'plus' => 1),
        'ar2'     => array('cols' => 'Referee', 'req' => true, 'plus' => 2),
    );
    public function __construct($gameRepo,$personRepo)
    {
        parent::__construct();
        
        $this->gameRepo   = $gameRepo;
        $this->personRepo = $personRepo;
    }
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        
        $game = $this->gameRepo->findOneByProjectNum($this->projectId,$num);
        
        if (!$game) return;
        
        $this->results->totalGameCount++;
        $isModified = false;
        
        $names = array(
            1 => $item['referee'],
            2 => $item['ar1'],
            3 => $item['ar2'],
        );
        
        for($slot = 1; $slot < 4; $slot++)
        {
            $official = $game->getOfficialForSlot($slot);
            
            // Always do name
            $officialName = $official->getPersonNameFull();
            if ($officialName != $names[$slot])
            {
                $official->setPersonNameFull($names[$slot]);
                if (!$isModified)
                {
                    $this->results->modifiedSlotCount++;
                    $isModified = true;
                }
            }
            // Link to person
            $person = $this->personRepo->findOneByProjectName($this->projectId,$officialName);
            $personGuid = $person ? $person->getGuid() : null;

            if ($personGuid != $official->getPersonGuid())
            {
                $official->setPersonGuid($personGuid);
                if (!$isModified)
                {
                    $this->results->modifiedSlotCount++;
                    $isModified = true;
                }
            }
            // Adjust slot status
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
        $this->results->modifiedSlotCount = 0;
        
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
