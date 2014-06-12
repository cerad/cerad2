<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByImport;

use Cerad\Bundle\CoreBundle\Event\FindPersonPlanEvent;

class AssignByImportSaveORMResults
{
    public $commit = false;
    
    public $basename;
    
    public $totalGameCount    = 0;
    
    public $unverifiedPersons = array();
    public $modifiedSlots = array();
    public $clearedSlots  = array();
}
class AssignByImportSaveORM
{
    protected $dispatcher;
    protected $gameRepo;
    
    protected $results;
    protected $state;
    protected $verify;

    public function __construct($dispatcher,$gameRepo)
    {   
        $this->dispatcher = $dispatcher;
        $this->gameRepo   = $gameRepo;
    }
    public function commit()
    {
        $this->gameRepo->commit();
    }
    /* ========================================================
     * The ability to add an unregistered person without a guid
     * makes this more messy then it should be
     * 
     * Need it for KAC stuff
     */
    protected function saveGameOfficial($game,$official,$state,$verify)
    {   
        $results = $this->results;
        
        $log = array(
            'game' => $game->getNum(),
            'slot' => $official['slot'],
            'name' => $official['personNameFull'],
        );
        
        $slot = $game->getOfficialForSlot($official['slot']);
        if (!$slot)
        {
            return; // VIP issues
            //
            die('No slot ' . $game->getLevelKey());
        }
        $slotPersonNameFull = $official['personNameFull'];
        
        // No name, clear slot
        if (!$slotPersonNameFull)
        {
            if ($slot->getPersonNameFull())
            {
                $results->clearedSlots[] = $log;
            }
            // Not really required
            $slot->setPersonFromPlan(null);
            $slot->setAssignState('Open');
            return;
        }
        // If the names match and have a guid then do nothing
        if ($slot->getPersonNameFull() == $slotPersonNameFull)
        {
            // Just in case have name but no guid
            if ($slot->getPersonGuid()) return;
        }
        // Link to person
        $findPersonPlanEvent =  new FindPersonPlanEvent($game->getProjectKey(),$slotPersonNameFull);
        $this->dispatcher->dispatch(FindPersonPlanEvent::FindByProjectNameEventName,$findPersonPlanEvent);
        
        $personPlan = $findPersonPlanEvent->getPlan();
        if (!$personPlan)
        {
            $results->unverifiedPersons[] = $log;
            
            if ($verify) 
            {
                $results->commit = false;
                return;
            }
            $slot->setPersonFromPlan(null);
            $slot->setPersonNameFull($slotPersonNameFull);
            $slot->setAssignState($state);
            
            return;
        }
        $slot->setPersonFromPlan($personPlan);
        $slot->setAssignState($state);

        $results->modifiedSlots[] = $log;
    }
    protected function saveGame($gamex,$state,$verify)
    {
        $game = $this->gameRepo->findOneByProjectNum($gamex['projectKey'],(int)$gamex['num']);
        
        if (!$game) 
        {
            // Log missing game
            print_r($gamex); die('Missing game ' . $gamex['num']);;
            return;
        }
        if (!isset($gamex['officials'])) return;
        
        foreach($gamex['officials'] as $official)
        {
            $this->saveGameOfficial($game,$official,$state,$verify);
        }
    }
     /* ==============================================================
     * Main entry point
     */
    public function save($games, $commit = false, $state = 'Pending', $verify = true)
    {
        $this->results = $results = new AssignByImportSaveORMResults();
        
        $results->commit = $commit;
        $results->totalGameCount = count($games);
        
        foreach($games as $game)
        {
            $this->saveGame($game,$state,$verify);
        }
        if ($results->commit) 
        {
            $this->gameRepo->commit();
        }
        return $results;
    }
}
?>
