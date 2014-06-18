<?php

namespace Cerad\Bundle\CoreBundle\Event;

class ControllerEventListenerPriority
{
    const CeradUser       = -1200;
    const CeradUserPerson = -1210;

    const CeradProject = -1300;
    
    const CeradPerson  = -1400;
    const CeradTeam    = -1500;
    const CeradGame    = -1600;    
}