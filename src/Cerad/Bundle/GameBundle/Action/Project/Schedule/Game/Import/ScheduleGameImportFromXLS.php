<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Import;

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
class ScheduleGameImportFromXLS extends BaseLoader
{
    protected $dispatcher;
    protected $gameRepo;
    protected $gameTransformer;
    
    protected $projectKey;
    
    protected $results;
    protected $state;
    protected $verify;
    
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
        
        'homeTeamGroup'  => array('cols' => 'Home Team Group', 'req' => true),
        'awayTeamGroup'  => array('cols' => 'Away Team Group', 'req' => true),
    );
    public function __construct($dispatcher,$gameRepo,$gameTransformer)
    {
        parent::__construct();
        
        $this->dispatcher = $dispatcher;
        $this->gameRepo   = $gameRepo;
        $this->gameTransformer = $gameTransformer;
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
        if (!$num) return;
        
        $this->results->totalGameCount++;
        
        print_r($item); die();
        
        
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
        
        
        $this->results = $results = new ImportResults();
        $results->commit   = $params['commit'];
        $results->basename = $params['basename'];
        
        // This loads and processes
        $this->load($params['filepath'],$workSheetName);
         
      //if ($results->commit) $this->gameRepo->commit();
        
        return $results;
    }
}
?>
