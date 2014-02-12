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
            
            GameEvents::GameOfficialAssignSlot  => array('onGameOfficialAssignSlot' ),
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
     * Game Official Assignment
     * Called before commit
     */
    public function onGameOfficialAssignSlot(AssignSlotEvent $event)
    {
        // Check for assignor notification
        $transition = $event->transition;
        if (!isset($transition['notifyAssignor'])) return;
        
        $tplData = array();
        $tplData['command']         = $event->command;
        $tplData['game']            = $event->gameOfficial->getGame();
        $tplData['gameOfficial']    = $event->gameOfficial;
        $tplData['gameOfficialOrg'] = $event->gameOfficialOrg;
        
        $templating = $this->container->get('templating');
        
        // Pull from project maybe? Use event->by?
        $tplEmailSubject = '@CeradGame/Project/GameOfficial/AssignByUser/AssignByUserEmailSubjectIndex.html.twig';
        $tplEmailContent = '@CeradGame/Project/GameOfficial/AssignByUser/AssignByUserEmailContentIndex.html.twig';
        
        $subject = $templating->render($tplEmailSubject,$tplData);
        $content = $templating->render($tplEmailContent,$tplData);
        
      //echo $subject . '<br />';
      //echo nl2br($content);
      //die();
        
        $adminName =  'Art Hundiak';
        $adminEmail = 'ahundiak@gmail.com';
        
        // This goes to the assignor
        $message1 = \Swift_Message::newInstance();
        $message1->setSubject($subject);
        $message1->setBody   ($content);
        $message1->setFrom(array('admin@zayso.org' => '[S1Games]'));
        $message1->setBcc (array($adminEmail => $adminName));
        
        $message1->setTo     (array($adminEmail => $adminName));
      //$message1->setTo     (array($assignorEmail => $assignorName));
      //$message1->setReplyTo(array($refereeEmail  => $refereeName));

        $this->container->get('mailer')->send($message1);

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
