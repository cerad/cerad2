<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FindPersonPlanEvent extends Event
{
    const FindByProjectGuidEventName = 'CeradPersonPlanFindByProjectGuid';
    const FindByProjectNameEventName = 'CeradPersonPlanFindByProjectName';
    
    protected $plan;
    protected $search;
    protected $project;
    
    public function __construct($project,$search)
    {
        $this->project = $project;
        $this->search = trim($search);
    }
    public function getPlan()      { return $this->plan;  }
    public function setPlan($plan) { $this->plan = $plan; }

    public function getProject() { return $this->project; }
    public function getSearch () { return $this->search;  }
}