<?php
namespace Cerad\Bundle\LevelBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/// Symfony\Component\HttpKernel\KernelEvents;
/// Symfony\Component\HttpKernel\Event\FilterControllerEvent;
/// Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/// Cerad\Bundle\CoreBundle\Event\ControllerEventListenerPriority;

use Cerad\Bundle\CoreBundle\Event\Level\FindProjectLevelsEvent;

class LevelEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            FindProjectLevelsEvent::Find  => array('onFindProjectLevels' ),
        );
    }
    protected function getLevelRepository()
    {
        return $this->container->get('cerad_level__level_repository');
    }
    /* ============================================================
     * Really don't have project levels yet but do need them
     */
    public function onFindProjectLevels(FindProjectLevelsEvent $event)
    {
        $levelRepo = $this->getLevelRepository();
        
        $levels = $levelRepo->findAllByPGA(
            $event->getProject(),
            $event->getPrograms(),
            $event->getGenders(),
            $event->getAges()
        );
        
        $event->setLevels($levels);
    }
}