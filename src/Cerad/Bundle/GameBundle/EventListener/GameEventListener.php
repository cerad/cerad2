<?php
namespace Cerad\Bundle\GameBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Cerad\Bundle\GameBundle\GameEvents;
use Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;

//  Cerad\Bundle\GameBundle\Service\GameOfficial\AssignSlot\AssignSlotWorkflow;

class GameEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::REQUEST => array(array('onKernelRequest', -16)), // Runs After ProjectListener
            
            GameEvents::GameOfficialAssignSlotPre  => array('onGameOfficialAssignSlotPre' ),
            GameEvents::GameOfficialAssignSlotPost => array('onGameOfficialAssignSlotPost'),
        );
    }
    protected $gameRepositoryServiceId;
    
    public function __construct($gameRepositoryServiceId)
    {
        $this->gameRepositoryServiceId = $gameRepositoryServiceId;
    }
    protected function getGameRepository()
    {
        return $this->container->get($this->gameRepositoryServiceId);
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        // Only process routes with a model
        $request = $event->getRequest();
        $gameNum = $request->attributes->get('_game');
        if (!$gameNum) return;
        
        // Must have already gotten the project
        if (!$request->attributes->has('project'))
        {
            // Could be invalid request?
            throw new NotFoundHttpException(sprintf('Project missing for game: %d',$gameNum));
        }
        $projectKey = $request->attributes->get('project')->getKey();
        
        $game = $this->getGameRepository()->findOneByProjectNum($projectKey,$gameNum);
        
        if ($game)
        {
            die('Found Game: ' . $game->getNum());
            $request->attributes->set('game',$game);
            return;
        }
        throw new NotFoundHttpException(sprintf('Game %s %d not found',$projectKey,$gameNum));
    }
    /* ====================================================================
     * Assignment stuff
     */
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
