<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FindPersonEvent extends Event
{
    const FindByGuidEventName = 'CeradPersonFindByGuid';
    
    protected $search;
    protected $person;
    
    public function __construct($search)
    {
        $this->search = trim($search);
    }
    public function getPerson()        { return $this->person;     }
    public function setPerson($person) { $this->person = $person; }

    public function getSearch() { return $this->search; }
}