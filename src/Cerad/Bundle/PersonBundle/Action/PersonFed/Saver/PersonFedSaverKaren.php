<?php

namespace Cerad\Bundle\PersonBundle\Action\PersonFed\Saver;

class PersonFedSaverKarenResults
{
    public $commit  = false;
    
    public $total   = 0;
    public $missing = 0;
    
    public $updatedMemYear = 0;
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
            $results->missing++;
            return;
        }
        if ($memYear > $personFed->getMemYear())
        {
          //print_r($item); die();
            $personFed->setMemYear($memYear);
            $results->updated++;
        }
        $personFed->setFedKeyVerified('Yes');
        $personFed->setPersonVerified('Yes');
        
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
