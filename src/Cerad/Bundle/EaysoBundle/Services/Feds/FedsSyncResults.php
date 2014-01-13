<?php
namespace Cerad\Bundle\EaysoBundle\Services\Feds;

use Symfony\Component\Stopwatch\Stopwatch;

class FedsSyncResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $countRows = 0;
    
    public $countFedMatch  = 0;
    public $countFedUpdate = 0;
    
    public $countCertMatch  = 0;
    public $countCertInsert = 0;
    public $countCertUpdate = 0;
    
    public $countPersonUpdate = 0;
    
    public $duration;
    public $memory;
    
    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start('sync');
    }
    public function __toString()
    {
        // Should probably not be here
        $event = $this->stopwatch->stop('sync');
        $this->duration = $event->getDuration();
        $this->memory = $event->getMemory();

        return  sprintf(
            "Sync Eayso Feds %s %s\n" . 
            "Feds   Rows   %d, Match  %d, Update %d\n" .
            "Certs  Update %d, Insert %d\n" .
            "Person Update %d\n" .
            "Duration %.2f %.2fM\n",
            $this->message,
            $this->basename,
            $this->countRows,
            $this->countFedMatch,
            $this->countFedUpdate,
            $this->countCertUpdate,
            $this->countCertInsert,
            $this->countPersonUpdate,
            $this->duration / 1000.,
            $this->memory   / 1000000.
        );
    }
}
?>
