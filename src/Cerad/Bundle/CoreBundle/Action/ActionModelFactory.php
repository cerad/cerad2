<?php

namespace Cerad\Bundle\CoreBundle\Action;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionModelFactory
{
    protected $dispatcher;

    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    public function setSecurityContext(SecurityContextInterface $securityContext) 
    { 
        $this->securityContext = $securityContext;
    }

}
