<?php

namespace Cerad\Bundle\CoreBundle\Event\Person;

use Symfony\Component\EventDispatcher\Event;

class FindProjectPersonEvent extends Event
{
    const ByName = 'CeradPersonFindProjectPersonByName';
    const ByGuid = 'CeradPersonFindProjectPersonByGuid';
    
    protected $search;
    protected $person = null;
    protected $project;
    
    public function __construct($project,$search)
    {
        $this->search  = $search;
        $this->project = $project;
    }
    public function getSearch () { return $this->search;  }
    public function getProject() { return $this->project; }

    public function getPerson()  { return $this->person;  }
    
    public function setPerson($person) { $this->person = $person; }
}