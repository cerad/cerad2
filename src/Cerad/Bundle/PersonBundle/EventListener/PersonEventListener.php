<?php
namespace Cerad\Bundle\PersonBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Cerad\Bundle\CoreBundle\Event\FindPersonEvent;

class PersonEventListener extends ContainerAware implements EventSubscriberInterface
{
    const ControllerPersonEventListenerPriority = -1400;
    
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::CONTROLLER => array(
                array('onControllerPerson', self::ControllerPersonEventListenerPriority),
            ),       
            FindPersonEvent::FindByGuidEventName  => array('onFindPersonByGuid' ),
        );
    }
    public static function getSubscribedEventsOld()
    {
        /*
        return array
        (
            PersonEvents::FindPersonById          => array('onFindPersonById'  ),
            PersonEvents::FindPersonByGuid        => array('onFindPersonByGuid'  ),
            PersonEvents::FindPersonByFedKey      => array('onFindPersonByFedKey'),
            PersonEvents::FindPersonByProjectName => array('onFindPersonByProjectName'),
            PersonEvents::FindOfficialsByProject  => array('onFindOfficialsByProject'),
            
            PersonEvents::FindPlanByProjectAndPerson => array('onFindPlanByProjectAndPerson'),
            
            PersonEvents::FindPersonPlanByProjectAndPersonGuid => array('onFindPersonPlanByProjectAndPersonGuid'),        
        ); */
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
    public function onControllerPerson(FilterControllerEvent $event)
    {
        // TODO: Finish implementation
        if (!$event->getRequest()->attributes->has('_person')) return;
    }
    public function onFindPersonByGuid(FindPersonEvent $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
        // Lookup
        $person = $this->getPersonRepository()->findOneByGuid($event->getSearch());
        
        $event->setPerson($person);
        
        return;
    }
    /* ========================================================
     * TODO: Review and update
     */
    public function onFindOfficialsByProject(Event $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
        $projectKey = $event->project->getKey();
        
        $event->officials = $this->getPersonRepository()->findOfficialsByProject($projectKey);        
    }
    public function onFindPlanByProjectAndPerson(Event $event)
    {
        $projectKey = $event->project->getKey();
        $personGuid = $event->person ->getGuid();
        
        $event->plan = $this->getPersonRepository()->findOnePersonPlanByProjectAndPersonGuid($projectKey,$personGuid);
        
        if ($event->plan) $event->stopPropagation();
    }
    public function onFindPersonPlanByProjectAndPersonGuid(Event $event)
    {
        $projectKey = $event->project->getKey();
        
        $event->personPlan = $this->getPersonRepository()->findOnePersonPlanByProjectAndPersonGuid($projectKey,$event->personGuid);
        
        if ($event->personPlan) $event->stopPropagation();
    }
    public function onFindPersonById(Event $event)
    {
        // Lookup
        $event->person = $this->getPersonRepository()->find($event->id);
        
        if ($event->person) $event->stopPropagation();
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
