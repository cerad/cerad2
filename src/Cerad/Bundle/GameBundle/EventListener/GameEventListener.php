<?php
namespace Cerad\Bundle\GameBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Cerad\Bundle\CoreBundle\EventListener\CoreRequestListener;

use Cerad\Bundle\GameBundle\GameEvents;
use Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;
use Cerad\Bundle\GameBundle\Event\FindResultsEvent;

class GameEventListener extends ContainerAware implements EventSubscriberInterface
{
    const ControllerGameEventListenerPriority = -1600;
    
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::CONTROLLER => array(
                array('onControllerGame', self::ControllerGameEventListenerPriority),
            ),
            
            FindResultsEvent::EventName  => array('onFindResults' ),
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
    public function onControllerGame(FilterControllerEvent $event)
    {
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
     * Finds the results/scoring service for a given project
     */
    public function onFindResults(FindResultsEvent $event)
    {
        $key = $event->getProject()->getResults();
        
        $resultsServiceId = sprintf('cerad_game__results_%s',$key);
        $results = $this->container->get($resultsServiceId);
        
        $event->setResults($results);
        $event->stopPropagation();
        return;
    }
    /* ====================================================================
     * Game Official Assignment
     * Called before commit
     * Ideally these events should be stored on some sort of internel que
     * and then processed as a group following a submit.
     * 
     * TODO: Consider moving this to the action directory
     */
    public function onGameOfficialAssignSlot(AssignSlotEvent $event)
    {
        // Check for assignor notification
        $transition = $event->transition;
        if (!isset($transition['notifyAssignor'])) return;
        
        // Make the subject and content
        $project = $event->project;
        $prefix  = $project->getPrefix();
        
        $tplData = array();
        $tplData['command']         = $event->command;
        $tplData['project']         = $project;
        $tplData['prefix']          = $prefix;
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
      
        // Assignor stuff
        $assignor = $project->getAssignor();
        $assignorName  = $assignor['name'];
        $assignorEmail = $assignor['email'];

        // Official stuff
        $gameOfficial = $event->gameOfficial;
        $gameOfficialName  = $gameOfficial->getPersonNameFull();
        $gameOfficialEmail = $gameOfficial->getPersonEmail();
        
        // From stuff
        // TODO: Research differences between natgames and s1games
        $fromName  = $prefix;
        $fromEmail = 'admin@zayso.org';
        
        // bcc stuff
        $adminName =  'Art Hundiak';
        $adminEmail = 'ahundiak@gmail.com';
        
        // This goes to the assignor
        $assignorMessage = \Swift_Message::newInstance();
        $assignorMessage->setSubject($subject);
        $assignorMessage->setBody   ($content);
        $assignorMessage->setFrom(array($fromEmail     => $fromName));
        $assignorMessage->setBcc (array($adminEmail    => $adminName));
        $assignorMessage->setTo  (array($assignorEmail => $assignorName));
        
        if ($gameOfficialEmail)
        {
            $assignorMessage->setReplyTo(array($gameOfficialEmail => $gameOfficialName));
        }
        
        // And send
        $this->container->get('mailer')->send($assignorMessage);

        return;
    }
}
?>
