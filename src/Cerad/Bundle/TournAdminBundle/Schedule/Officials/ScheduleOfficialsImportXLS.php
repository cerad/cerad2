<?php
namespace Cerad\Bundle\TournAdminBundle\Schedule\Officials;

use Doctrine\Common\PropertyChangedListener;

use Cerad\Component\Excel\Loader as BaseLoader;

class ImportResults implements PropertyChangedListener
{
    public $basename;
    
    public $totalGameCount    = 0;
    public $modifiedSlotCount = 0;
    
    protected $slots = array();
    
    public function propertyChanged($slot, $propName, $oldValue, $newValue)
    {
        $slotId = $slot->getId();
        if (isset($this->slots[$slotId])) return;
        
        $this->modifiedSlotCount++;
        $this->slots[$slotId] = true;
        
        return;
    }
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
    /* ========================================================
     * Really need to hook a listener for changes
     */
    protected function processSlot($slot,$slotName)
    {
        $slot->addPropertyChangedListener($this->results);
        
        // No name, clear slot
        if (!$slotName)
        {
            // Probable need a rest method
            $slot->setState(null);
            $slot->setPersonGuid(null);
            $slot->setPersonNameFull(null);
            return;
        }
        // Link to person
        $person = $this->personRepo->findOneByProjectName($this->projectId,$slotName);
        $personGuid = $person ? $person->getGuid() : null;
        
        if ($personGuid)
        {
            // Always sync name
            $slot->setPersonNameFull($slotName);
             
            // See if an update
            if ($personGuid == $slot->getPersonGuid()) return;
            
            // Set default state
            $slot->setPersonGuid($personGuid);
            $slot->setState('Pending');
            return;
        }
        
        // No link
        $slot->setPersonGuid(null);
        
        if ($slotName == $slot->getPersonNameFull()) return;
        
        $slot->setPersonNameFull($slotName);
        $slot->setState('Pending');
        
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
        
        for($slotNum = 1; $slotNum < 4; $slotNum++)
        {
            $slot = $game->getOfficialForSlot($slotNum);
            $this->processSlot($slot,  $names[$slotNum]);          
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
