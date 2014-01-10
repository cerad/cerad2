<?php
namespace Cerad\Bundle\EaysoBundle\Services\Feds;

use Symfony\Component\Stopwatch\Stopwatch;

class VolsSyncResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $countLines = 0;
    
    public $countFedMatch  = 0;
    public $countFedUpdate = 0;
    
    public $countCertRead   = 0;
    public $countCertMatch  = 0;
    public $countCertInsert = 0;
    public $countCertUpdate = 0;
    
    public $countRegionInsert = 0;
    public $countRegionUpdate = 0;
    
    public $countPersonUpdate = 0;
    
    public $duration;
    
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

        return  sprintf(
            "Sync Eayso Vols %s %s\n" . 
            "Vols   Lines  %d, Match  %d, Update %d\n" .
            "Person Update %d\n" .
            "Region Update %d, Insert %d\n" .
            "Duration %d\n",
            $this->message,
            $this->basename,
            $this->countLines,
            $this->countFedMatch,
            $this->countFedUpdate,
            $this->countPersonUpdate,
            $this->countRegionUpdate,
            $this->countRegionInsert,
            $this->duration
        );
    }
}
?>
