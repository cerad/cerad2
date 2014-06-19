<?php
namespace Cerad\Bundle\PersonBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Cerad\Bundle\CoreBundle\Event\ControllerEventListenerPriority;

use Cerad\Bundle\CoreBundle\Event\FindPersonEvent;
use Cerad\Bundle\CoreBundle\Event\FindPersonPlanEvent;
use Cerad\Bundle\CoreBundle\Event\FindOfficialsEvent;

use Cerad\Bundle\CoreBundle\Event\Person\FindProjectPersonEvent;
use Cerad\Bundle\CoreBundle\Event\Person\FindProjectPersonTeamsEvent;

class PersonEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::CONTROLLER => array(
                array('onControllerPerson', ControllerEventListenerPriority::CeradPerson),
            ),       
            FindPersonEvent::FindByGuidEventName   => array('onFindPersonByGuid' ),
            FindPersonEvent::FindByFedKeyEventName => array('onFindPersonByFedKey' ),
            
            FindProjectPersonEvent::ByGuid  => array('onFindProjectPersonByGuid' ),
            FindProjectPersonEvent::ByName  => array('onFindProjectPersonByName' ),
            
            FindProjectPersonTeamsEvent::ByGuid  => array('onFindProjectPersonTeams' ),
            
            FindPersonPlanEvent::FindByProjectGuidEventName  => array('onFindPersonPlanByProjectGuid' ),
            FindPersonPlanEvent::FindByProjectNameEventName  => array('onFindPersonPlanByProjectName' ),
            
            FindOfficialsEvent ::FindOfficialsEventName      => array('onFindOfficials' ),
        );
    }
    protected function getPersonRepository()
    {
        return $this->container->get('cerad_person__person_repository');
    }
    protected function getPersonTeamRepository()
    {
        return $this->container->get('cerad_person__person_team_repository');
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
    /* ===============================================================================
     * Finds an aggrate person with attached project plan as well as project fed info
     */
    public function onFindProjectPersonByGuid(FindProjectPersonEvent $event)
    {
        // Probably not needed
        $personGuid = $event->getSearch();
        if (!$personGuid) return;
        
        // Project
        $project = $event->getProject();
        if (!$project)
        {
            throw new \InvalidArgumentException('FindProjectPersonByGuid:: Missing Project');
        }
        if (!is_object($project))
        {
            throw new \InvalidArgumentException('FindProjectPersonByGuid:: Project Key Not yet Supported');
        }
        
        // Lookup
        $person = $this->getPersonRepository()->findProjectPersonByGuid($project,$personGuid);
        if (!$person)
        {
            return;
        }
        $event->setPerson($person);
    }
    /* ===============================================================================
     * Finds an aggrate person with attached project plan as well as project fed info
     */
    public function onFindProjectPersonTeams(FindProjectPersonTeamsEvent $event)
    {
        // Probably not needed
        $personGuids = $event->getPersons();
        
        // Project
        $project = $event->getProject();
        
        // Lookup
        $personTeams = $this->getPersonTeamRepository()->findAllByProjectPerson($project,$personGuids);

        $event->setPersonTeams($personTeams);
    }
    public function onFindProjectPersonByName(FindProjectPersonEvent $event)
    {
        // Probably not needed
        $personName = $event->getSearch();
        if (!$personName) return;
        
        // Project
        $project = $event->getProject();
        if (!$project)
        {
            throw new \InvalidArgumentException('FindProjectPersonByGuid:: Missing Project');
        }
        if (!is_object($project))
        {
            throw new \InvalidArgumentException('FindProjectPersonByGuid:: Project Key Not yet Supported');
        }
        
        // Lookup
        $person = $this->getPersonRepository()->findProjectPersonByName($project,$personName);
        if (!$person)
        {
            return;
        }
        $event->setPerson($person);
    }
}
?>
