<?php

namespace Cerad\Bundle\CoreBundle\Event\Person;

use Symfony\Component\EventDispatcher\Event;

class FindProjectPersonTeamsEvent extends Event
{
    const ByGuid = 'CeradPersonFindProjectPersonTeamsByGuid';
    
    protected $personTeams = array();
    
    protected $persons = array();
    protected $project;
    
    public function __construct($project,$persons)
    {
        $this->persons = $persons;
        $this->project = $project;
    }
    public function getPersons() { return $this->persons; }
    public function getProject() { return $this->project; }

    public function getPersonTeams()  { return $this->personTeams;  }
    
    public function setPersonTeams($personTeams) { $this->personTeams = $personTeams; }
}