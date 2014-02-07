<?php
namespace Cerad\Bundle\GameBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Cerad\Bundle\GameBundle\Events;
use Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;

//  Cerad\Bundle\GameBundle\Service\GameOfficial\AssignSlot\AssignSlotWorkflow;

class GameEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            Events::GameOfficialAssignSlotPre  => array('onGameOfficialAssignSlotPre' ),
            Events::GameOfficialAssignSlotPost => array('onGameOfficialAssignSlotPost'),
        );
    }
    protected function getAssignSlotWorkflow()
    {
        return $this->container->get('cerad_game__game_official__assign_slot_workflow');
    }
    public function onGameOfficialAssignSlotPre(AssignSlotEvent $event)
    {
        return;
    }
    public function onGameOfficialAssignSlotPost(AssignSlotEvent $event)
    {
        $assignSlotWorkflow = $this->getAssignSlotWorkflow();
        
        $gameOfficialNew = $event->gameOfficialNew;
        $gameOfficialOld = $event->gameOfficialOld;
        
        if (!$assignSlotWorkflow->notifyAssignor($gameOfficialNew,$gameOfficialOld)) return;
        
        die('notify assignor');
        $assignState = $gameOfficialNew->getAssignState();
        switch($state)
        {
            
        }
        return;
    }
}
?>
