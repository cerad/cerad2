<?php

namespace Cerad\Bundle\PersonBundle\Action\PersonFed\Saver;

/*
 *     
 * fedKey: AYSOV53305279
    name: 'Craig Arnall'
    email: craigarnall@bak.rr.com
    phone: 661.979.5232
    region: 359
    memYear: MY2013
    safeHaven: AYSO
 */

class PersonFedSaverThemResults
{
    public $commit  = false;
    
    public $total   = 0;
    public $missing = array();
    
    public $updatedMemYear   = 0;
    public $updatedSafeHaven = 0;
  //public $updatedRegion  = 0;
}
class PersonFedSaverThem
{
    protected $results;
    
    protected $personFedRepo;
        
    protected $dispatcher;
    
    public function __construct($personFedRepo)
    {
        $this->personFedRepo = $personFedRepo;
    }
    public function setDispatcher($dispatcher) { $this->dispatcher = $dispatcher; }
    
    /* =============================================
     */
    protected function savePersonFed($results,$item)
    {   
        $fedKey    = $item['fedKey'];
        $memYear   = $item['memYear'];
        $safeHaven = $item['safeHaven'];
        
        $changed = false;
        
        $personFed = $this->personFedRepo->findOneByFedKey($fedKey);
        if (!$personFed)
        {
            $results->missing[] = $item;
            return;
        }
        if ($memYear > $personFed->getMemYear())
        {
          //print_r($item); die();
            $personFed->setMemYear($memYear);
            $results->updatedMemYear++;
            $changed = true;
        }
        $personFed->setFedKeyVerified('Yes');
        
        if ($memYear && $safeHaven)
        {
            // Ignore safe haven since we were not getting that info
            $personFed->setPersonVerified('Yes');
            $changed = true;
        }
        $certSafeHaven = $personFed->getCertSafeHaven(true);
        if (!$certSafeHaven->getBadge()) 
        {
          //print_r($item); die();
            $certSafeHaven->setBadge($safeHaven);
            $results->updatedSafeHaven++;
            $changed = true;
        }
        // Ignore region for now, not sure I trust THEM
        
        // TODO
        if ($changed)
        {
            // dispatch event
        }
    }
    /* ==============================================================
     * Main entry point
     */
    public function save($personFeds,$commit = false)
    {
        $this->results = $results = new PersonFedSaverThemResults();
        
        $results->commit = $commit;
        $results->total = count($personFeds);
        
        foreach($personFeds as $item)
        {
            $this->savePersonFed($results,$item);
        }
         
        if ($results->commit) $this->commit();
        
        return $results;
    }
    public function commit()
    {
        $this->personFedRepo->commit();
    }
}
?>
