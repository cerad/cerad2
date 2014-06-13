<?php
namespace Cerad\Bundle\PersonBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            FindPersonEvent    ::FindByGuidEventName   => array('onFindPersonByGuid' ),
            FindPersonEvent    ::FindByFedKeyEventName => array('onFindPersonByFedKey' ),
            
            FindPersonPlanEvent::FindByProjectGuidEventName  => array('onFindPersonPlanByProjectGuid' ),
            FindPersonPlanEvent::FindByProjectNameEventName  => array('onFindPersonPlanByProjectName' ),
            
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
        if (!$event->getRequest()->attributes->has('_person')) return;
        
        $_person = $event->getRequest()->attributes->has('_person');
        
        $person = $this->getPersonRepository()->find($_person);
        
        if (!$person)
        {
            $person = $this->getPersonRepository()->findOneByGuid($_person);
        }
        if (!$person)
        {
            // Maybe search by fedKey?
            throw new NotFoundHttpException(sprintf('Person %s not found',$_person));
        }
        $event->getRequest()->attributes->set('person',$person);
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
    public function onFindPersonByFedKey(Event $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
        // Lookup
        $fedKey = $event->getSearch();
        
        $personRepo = $this->getPersonRepository();
        
        $person = $personRepo->findOneByFedKey($fedKey);
        
        if ($person)
        {
            $event->setPerson($person);
            return;
        }
        
        // Try different prefixes, inject these later
        foreach(array('AYSOV','USSFC','NFHSC') as $prefix)
        {
            $person = $personRepo->findOneByFedKey($prefix . $fedKey);
            if ($person)
            {
                $event->setPerson($person);
                return;
            }
        }
    }
    /* =======================================================
     * This would be a good one to move to it's own service
     * Link person to their project plan as well as their fedkey info
     * 
     * Game is currently optional, make it required later
     * That would require accessing FedRole from somewhere
     */
    public function onFindOfficials(FindOfficialsEvent $event)
    {
        // Just means a listener was available
        $event->stopPropagation();
        
        $project = $event->getProject();
        
        $projectKey = $project->getKey();
        
        $game = $event->getGame();
        if ($game)
        {
            $projectKey = $game->getProjectKey();
            
            $levelParts = explode('_',$game->getLevelKey());
            
            $program = strtolower($levelParts[2]);
        }
        else $program = null;
        
        $officials = $this->getPersonRepository()->findOfficialsByProject($projectKey,$program,$project->getFedRole());
 
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
    public function onFindPersonPlanByProjectName(FindPersonPlanEvent $event)
    {
        $project    = $event->getProject();
        $personName = $event->getSearch();
        
        $plan = $this->getPersonRepository()->findOnePersonPlanByProjectAndPersonName($project,$personName);
        
        if ($plan) 
        {
            $event->setPlan($plan);
            $event->stopPropagation();
        }
    }
    /* ========================================================
     * TODO: Review and update
     */
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
}
?>
