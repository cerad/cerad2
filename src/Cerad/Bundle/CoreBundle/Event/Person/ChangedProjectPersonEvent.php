<?php

namespace Cerad\Bundle\CoreBundle\Event\Person;

use Symfony\Component\EventDispatcher\Event;

class ChangedProjectPersonEvent extends Event
{
    const Changed = 'CeradPersonChangedProjectPerson';
    
    protected $projectPerson;
    
    public function __construct($projectPerson)
    {
        $this->projectPerson = $projectPerson;
    }
    public function getProjectPerson() { return $this->projectPerson; }
}