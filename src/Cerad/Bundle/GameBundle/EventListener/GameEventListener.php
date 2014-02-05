<?php
namespace Cerad\Bundle\GameBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Cerad\Bundle\GameBundle\Events;
use Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;

class GameEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            Events::GAME_OFFICIAL_ASSIGN_SLOT__PRE  => array('onGameOfficialAssignSlotPre' ),
            Events::GAME_OFFICIAL_ASSIGN_SLOT__POST => array('onGameOfficialAssignSlotPost'),
        );
    }
    public function onGameOfficialAssignSlotPre(AssignSlotEvent $event)
    {
        return;
    }
    public function onGameOfficialAssignSlotPost(AssignSlotEvent $event)
    {
      //die('assign slot post event');
        return;
    }
}
?>
