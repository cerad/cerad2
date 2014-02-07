<?php
namespace Cerad\Bundle\PersonBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Cerad\Bundle\PersonBundle\Events;

class PersonEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            Events::FindPersonByGuid        => array('onFindPersonByGuid'  ),
            Events::FindPersonByFedKey      => array('onFindPersonByFedKey'),
            Events::FindPersonByProjectName => array('onFindPersonByProjectName'),
        );
    }
    protected function getPersonRepository()
    {
        return $this->container('cerad_person.person_repository');
    }
    public function onFindPersonByGuid(Event $event)
    {
        // Just means a listener was available
        $event->processed = true;
        
        // Lookup
        $event->person = $this->getPersonRepository()->findOneByGuid($event->guid);
        
        return;
    }
    public function onFindPersonByProjectName(Event $event)
    {
        // Just means a listener was available
        $event->processed = true;
        
        // Lookup
        $event->person = $this->getPersonRepository()->findOneByByProjectName($event->projectKey,$event->personName);
        
        return;
    }
    public function onFindPersonByFedKey(Event $event)
    {
        // Just means a listener was available
        $event->processed = true;
        
        // Extract
        $fedKey = $event->fedKey;
        if (!$fedKey) return;
        
        // Lookup
        $personRepo = $this->getPersonRepository();
        $event->person = $personRepo->findOneByFedKey($fedKey);
        if ($event->person) return;
        
        // Try different prefixes
        foreach(array('AYSOV','USSFC','NFHSC') as $prefix)
        {
            $event->person = $personRepo->findOneByFedKey($prefix . $fedKey);
            if ($event->person) return;
        }
        return;
    }
}
?>
