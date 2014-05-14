<?php
namespace Cerad\Bundle\PersonBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Cerad\Bundle\CoreBundle\Event\FindPersonEvent;
use Cerad\Bundle\CoreBundle\Event\FindPersonPlanEvent;
use Cerad\Bundle\CoreBundle\Event\FindOfficialsEvent;

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
            FindPersonEvent    ::FindByGuidEventName         => array('onFindPersonByGuid' ),
            
            FindPersonPlanEvent::FindByProjectGuidEventName  => array('onFindPersonPlanByProjectGuid' ),
            
            FindOfficialsEvent ::FindOfficialsEventName      => array('onFindOfficials' ),
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
    public function onFindOfficials(FindOfficialsEvent $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
        $projectKey = $event->getProject()->getKey();
        
        $officials = $this->getPersonRepository()->findOfficialsByProject($projectKey);
        
        $event->setOfficials($officials);
    }
    public function onFindPersonPlanByProjectGuid(FindPersonPlanEvent $event)
    {
        $projectKey = $event->getProject()->getKey();
        $personGuid = $event->getSearch();
        
        $plan = $this->getPersonRepository()->findOnePersonPlanByProjectAndPersonGuid($projectKey,$personGuid);
        
        if ($plan) 
        {
            $event->setPlan($plan);
            $event->stopPropagation();
        }
    }
    /* ========================================================
     * TODO: Review and update
     */
    public function onFindPlanByProjectAndPerson(Event $event)
    {
        $projectKey = $event->project->getKey();
        $personGuid = $event->person ->getGuid();
        
        $event->plan = $this->getPersonRepository()->findOnePersonPlanByProjectAndPersonGuid($projectKey,$personGuid);
        
        if ($event->plan) $event->stopPropagation();
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
