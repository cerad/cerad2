<?php
namespace Cerad\Bundle\GameBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
//  Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Cerad\Bundle\CoreBundle\Event\ControllerEventListenerPriority;

use Cerad\Bundle\CoreBundle\Event\Team\FindTeamEvent;

use Cerad\Bundle\CoreBundle\Event\FindProjectTeamsEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectLevelsEvent;

class TeamEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::CONTROLLER => array(
                array('onControllerTeam', ControllerEventListenerPriority::CeradTeam),
            ),           
            FindTeamEvent::ByKey  => array('onFindTeamByKey'),
            
            FindProjectTeamsEvent::Find  => array('onFindProjectTeams'),
        );
    }
    protected function getTeamRepository()
    {
        return $this->container->get('cerad_game__team_repository');
    }
    public function onControllerTeam(FilterControllerEvent $event)
    {
        return; if ($event);
    }
    public function onFindTeamByKey(FindTeamEvent $event)
    {
        $teamKey = $event->getTeamKey();
        $team = $this->getTeamRepository()->findOneByKey($teamKey);
        $event->setTeam($team);
    }
    public function onFindProjectTeams(FindProjectTeamsEvent $event)
    {
        $project  = $event->getProjectKey();
        $programs = $event->getPrograms();
        $genders  = $event->getGenders();
        $ages     = $event->getAges();
        
        $findLevelsEvent = new FindProjectLevelsEvent($project,$programs,$genders,$ages);
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(FindProjectLevelsEvent::FindProjectLevels,$findLevelsEvent);
        $levelKeys = $findLevelsEvent->getLevelKeys();
        
        $teamRepo = $this->getTeamRepository();
        $teams = $teamRepo->findAllByProjectLevels($project,$levelKeys);
        
        $event->setTeams($teams);
        $event->stopPropagation();
    }
}
?>
