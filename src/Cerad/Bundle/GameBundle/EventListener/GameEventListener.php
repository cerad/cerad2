<?php
namespace Cerad\Bundle\GameBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Cerad\Bundle\CoreBundle\EventListener\CoreRequestListener;

use Cerad\Bundle\GameBundle\GameEvents;
use Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;

class GameEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::REQUEST => array(array('onKernelRequest', CoreRequestListener::GameEventListenerPriority)),
            
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
        // Will a sub request ever change projects?
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) return;
        
        // Only process routes asking for a game
        if (!$event->getRequest()->attributes->has('_game')) return;
        
        // Pull the game number
        $request = $event->getRequest();
        $gameNum = $request->attributes->get('_game');
        
        // Must have already gotten the project
        if (!$request->attributes->has('project'))
        {
            // Could be invalid request?
            throw new NotFoundHttpException(sprintf('Project missing for game: %d',$gameNum));
        }
        $projectKey = $request->attributes->get('project')->getKey();
        
        // Query Game
        $game = $this->getGameRepository()->findOneByProjectNum($projectKey,$gameNum);
        if (!$game)
        {
            throw new NotFoundHttpException(sprintf('Game %s %d not found',$projectKey,$gameNum));
        }
        // Stash It
        $request->attributes->set('game',$game);
        
        // Check for game official
        if ($request->attributes->has('_game_official')) 
        {
            $slot = $request->attributes->get('_game_official');
            $gameOfficial = $game->getOfficialForSlot($slot);
            if (!$gameOfficial)
            {
                throw new NotFoundHttpException(sprintf('Game Official %s %d %d not found',$projectKey,$gameNum,$slot));
            }
            $request->attributes->set('gameOfficial',$gameOfficial);
        }
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
