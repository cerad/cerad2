<?php
namespace Cerad\Bundle\ProjectBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Cerad\Bundle\CoreBundle\Event\ControllerEventListenerPriority;

use Cerad\Bundle\CoreBundle\Event\FindProjectEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectLevelsEvent;

class ProjectEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::CONTROLLER => array(
                array('onControllerProject', ControllerEventListenerPriority::CeradProject),
            ),

            FindProjectEvent::FindProjectByKey  => array('onFindProjectByKey'  ),
            FindProjectEvent::FindProjectBySlug => array('onFindProjectBySlug' ),
            
            FindProjectLevelsEvent::FindProjectLevels => array('onFindProjectLevels' ),
        );
    }
    protected $projectSlugDefault;
    protected $projectRepositoryServiceId;
    
    public function __construct($projectRepositoryServiceId,$projectSlugDefault = null)
    {
        $this->projectRepositoryServiceId = $projectRepositoryServiceId;
        
        $this->projectSlugDefault = $projectSlugDefault;
    }
    protected function getProjectRepository()
    {
        return $this->container->get($this->projectRepositoryServiceId);
    }
    public function onControllerProject(FilterControllerEvent $event)
    {
        // Pull project from _project or from the default
        if (!$event->getRequest()->attributes->has('_project')) 
        {
            if (!$this->projectSlugDefault) return;
            $event->getRequest()->attributes->set('_project',$this->projectSlugDefault);
        }
        $projectSlug = $event->getRequest()->attributes->get('_project');
      
        // Query the project
        $project = $this->getProjectRepository()->findOneBySlug($projectSlug);
        if (!$project)
        {
            throw new NotFoundHttpException(sprintf('Project %s not found',$projectSlug));
        }
        // Stash it
        $event->getRequest()->attributes->set('project',$project);
        $this->container->set('cerad_project__request', $project);
        
        // Twig global
        $twig = $this->container->get('twig');
        $twig->addGlobal( 'project',$project);
        $twig->addGlobal('_project',$projectSlug);        
    }
    public function onFindProjectBySlug(FindProjectEvent $event)
    {
        // Lookup
        $event->stopPropagation();
        $project = $this->getProjectRepository()->findOneBySlug($event->getSearch());
        $event->setProject($project);
        return;
    }
    public function onFindProjectByKey(FindProjectEvent $event)
    {
        // Lookup
        $event->stopPropagation();
        $project = $this->getProjectRepository()->findOneByKey($event->getSearch());
        $event->setProject($project);
        return;
    }
    /* ============================================================
     * Really don't have project levels yet but do need them
     */
    public function onFindProjectLevels(FindProjectLevelsEvent $event)
    {
        $criteria = array(
            'programs' => $event->getPrograms(),
            'genders'  => $event->getGenders(),
            'ages'     => $event->getAges(),
        );
        $levelRepo = $this->container->get('cerad_level__level_repository');
        $levelKeys = $levelRepo->queryKeys($criteria);
        
        $event->setLevelKeys($levelKeys);
        $event->stopPropagation();
    }
}