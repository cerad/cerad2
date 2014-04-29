<?php
namespace Cerad\Bundle\GameBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FindResultsEvent extends Event
{
    const EventName = 'CeradGameFindResults';
    
    protected $project;
    protected $results;
    
    public function __construct($project)
    {
        $this->project = $project;
    }
    public function getProject() { return $this->project; }
    public function getResults() { return $this->results; }
    
    public function setResults($results) { $this->results = $results; return $this; }
    
}
