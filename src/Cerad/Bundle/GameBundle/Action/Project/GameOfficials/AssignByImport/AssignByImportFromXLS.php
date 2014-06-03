<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByImport;

use Cerad\Component\Excel\Loader as BaseLoader;

use Cerad\Bundle\CoreBundle\Event\FindPersonPlanEvent;

class ImportResults
{
    public $commit = false;
    
    public $basename;
    
    public $totalGameCount    = 0;
    public $modifiedSlotCount = 0;
    public $clearedSlotCount  = 0;
    public $personNameSlotCount = 0;
    
    public $unverifiedPersons = array();
    public $modifiedSlots = array();
    public $clearedSlots  = array();
}
class AssignByImportFromXLS extends BaseLoader
{
    protected $dispatcher;
    protected $gameRepo;
    protected $projectKey;
    
    protected $results;
    protected $state;
    protected $verify;
    
    protected $record = array
    (
        'num'     => array('cols' => 'Game',    'req' => true),
        'referee' => array('cols' => 'Referee', 'req' => true),
        'ar1'     => array('cols' => 'Referee', 'req' => true, 'plus' => 1),
        'ar2'     => array('cols' => 'Referee', 'req' => true, 'plus' => 2),
    );
    public function __construct($dispatcher,$gameRepo)
    {
        parent::__construct();
        
        $this->dispatcher = $dispatcher;
        $this->gameRepo   = $gameRepo;
    }
    /* ========================================================
     * The ability to add an unregistered person without a guid
     * makes this more messy then it should be
     */
    protected function processSlot($game,$slot,$slotPersonName)
    {   
        $results = $this->results;
        
        $log = array('game' => $game->getNum(),'slot' => $slot->getRole(),'name' => $slotPersonName);
        
        // No name, clear slot
        if (!$slotPersonName)
        {
            if ($slot->getPersonNameFull())
            {
                $results->clearedSlotCount++;
                $results->clearedSlots[] = $log;
            }
            // Not really required
            $slot->setPersonFromPlan(null);
            $slot->setAssignState('Open');
            return;
        }
        // If the names match and have a guid then do nothing
        if ($slot->getPersonNameFull() == $slotPersonName)
        {
            // Just in case have name but no guid
            if ($slot->getPersonGuid()) return;
        }
        // Link to person
        $findPersonPlanEvent =  new FindPersonPlanEvent($this->projectKey,$slotPersonName);
        $this->dispatcher->dispatch(FindPersonPlanEvent::FindByProjectNameEventName,$findPersonPlanEvent);
        
        $personPlan = $findPersonPlanEvent->getPlan();
        if (!$personPlan)
        {
            $results->personNameSlotCount++;
            $results->unverifiedPersons[] = $log;
            
            if ($this->verify) 
            {
                $results->commit = false;
                return;
            }
            $slot->setPersonFromPlan(null);
            $slot->setPersonNameFull($slotPersonName);
            $slot->setAssignState($this->state);
            return;
        }
        $slot->setPersonFromPlan($personPlan);
        $slot->setAssignState($this->state);
        $results->modifiedSlotCount++;  
        $results->modifiedSlots[] = $log;
    }
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        
        $game = $this->gameRepo->findOneByProjectNum($this->projectKey,$num);
        
        if (!$game) return;
        
        $this->results->totalGameCount++;
        
        $names = array(
            1 => $item['referee'],
            2 => $item['ar1'],
            3 => $item['ar2'],
        );
        
        for($slotNum = 1; $slotNum < 4; $slotNum++)
        {
            $slot = $game->getOfficialForSlot($slotNum);
            $this->processSlot($game,$slot,$names[$slotNum]);       
        }
    }
    /* ==============================================================
     * Almost like the load but with a few tewaks
     */
    public function process($params)
    {
        $project = $params['project'];
        $this->projectKey = $project->getKey();
        
        $workSheetName = isset($params['workSheetName']) ? $params['workSheetName'] : null;
        
        $this->state    = $params['state'];
        $this->verify   = $params['verify'];
        
        $this->results = $results = new ImportResults();
        $results->commit   = $params['commit'];
        $results->basename = $params['basename'];
        
        // This loads and processes
        $this->load($params['filepath'],$workSheetName);
         
        if ($results->commit) $this->gameRepo->commit();
        
        return $results;
    }
}
?>
