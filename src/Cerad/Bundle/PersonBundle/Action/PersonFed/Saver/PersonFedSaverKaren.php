<?php

namespace Cerad\Bundle\PersonBundle\Action\PersonFed\Saver;

class PersonFedSaverKarenResults
{
    public $commit  = false;
    
    public $total   = 0;
    public $missing = array();
    
    public $updatedMemYear   = 0;
    public $updatedSafeHaven = 0;
  //public $updatedRegion  = 0;
}
class PersonFedSaverKaren
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
     * TODO: Implement delete with negative number
     */
    protected function savePersonFed($results,$item)
    {   
        $fedKey  = $item['fedKey'];
        $memYear = $item['memYear'];
        
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
        }
        $personFed->setFedKeyVerified('Yes');
        $personFed->setPersonVerified('Yes');
        
        $certSafeHaven = $personFed->getCertSafeHaven(true);
        if (!$certSafeHaven->getBadge()) 
        {
          //print_r($item); die();
            $certSafeHaven->setBadge($item['safeHaven']);
            $results->updatedSafeHaven++;
        }
    }
    /* ==============================================================
     * Main entry point
     */
    public function save($personFeds,$commit = false)
    {
        $this->results = $results = new PersonFedSaverKarenResults();
        
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
