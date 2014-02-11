<?php
namespace Cerad\Bundle\PersonBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Cerad\Bundle\PersonBundle\PersonEvents;

class PersonEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            PersonEvents::FindPersonByGuid        => array('onFindPersonByGuid'  ),
            PersonEvents::FindPersonByFedKey      => array('onFindPersonByFedKey'),
            PersonEvents::FindPersonByProjectName => array('onFindPersonByProjectName'),
            PersonEvents::FindOfficialsByProject  => array('onFindOfficialsByProject'),
        );
    }
    protected $personRepositoryServiceId;
    
    public function __construct($personRepositoryServiceId)
    {
        $this->personRepositoryServiceId = $personRepositoryServiceId;
    }
    protected function getPersonRepository()
    {
        return $this->container->get($this->personRepositoryServiceId);
    }
    public function onFindOfficialsByProject(Event $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
        $projectKey = $event->project->getKey();
        
        $event->officials = $this->getPersonRepository()->findOfficialsByProject($projectKey);        
    }
    public function onFindPersonByGuid(Event $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
        // Lookup
        $event->person = $this->getPersonRepository()->findOneByGuid($event->guid);
        
        return;
    }
    public function onFindPersonByProjectName(Event $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
        // Lookup
        $event->person = $this->getPersonRepository()->findOneByByProjectName($event->projectKey,$event->personName);
        
        return;
    }
    public function onFindPersonByFedKey(Event $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
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
