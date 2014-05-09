<?php
namespace Cerad\Bundle\UserBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Cerad\Bundle\CoreBundle\Event\FindPersonEvent;

class UserEventListener extends ContainerAware implements EventSubscriberInterface
{
    const ControllerUserEventListenerPriority       = -1200;
    const ControllerUserPersonEventListenerPriority = -1210;
    
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::CONTROLLER => array(
                array('onControllerUser',       self::ControllerUserEventListenerPriority),
                array('onControllerUserPerson', self::ControllerUserPersonEventListenerPriority),
            ),
/*            
            FindResultsEvent::EventName  => array('onFindResults' ),
            GameEvents::GameOfficialAssignSlot  => array('onGameOfficialAssignSlot' ), */
        );
    }
    protected $userRepositoryServiceId;
    
    public function __construct($userRepositoryServiceId)
    {
        $this->userRepositoryServiceId = $userRepositoryServiceId;
    }
    protected function getUserRepository()
    {
        return $this->container->get($this->userRepositoryServiceId);
    }
    public function onControllerUser(FilterControllerEvent $event)
    {
        // Only process routes asking for a game
        if (!$event->getRequest()->attributes->has('_user')) return;
        if ( $event->getRequest()->attributes->has( 'user')) return;
        
        $securityContext = $this->container->get('security.context');
        
        // Follow the logic in S2 Controller::getUser
        $event->getRequest()->attributes->set('user',null);
        
        $token = $securityContext->getToken();
        if (!$token) return;
        
        $user = $token->getUser();
        if (!is_object($user)) return;
      
        $event->getRequest()->attributes->set('user',$user);
    }
    public function onControllerUserPerson(FilterControllerEvent $event)
    {
        if (!$event->getRequest()->attributes->has('_userPerson')) return;
        
        // Need a user first
        $request = $event->getRequest();
        if (!$request->attributes->has('user'))
        {
            $request->attributes->set('_user',true);
            $this->onControllerUser($event);
        }
        $user = $request->attributes->get('user');
        
        if (!$user) return;
        
        // Find The Person
        $findPersonEvent = new FindPersonEvent($user->getPersonGuid());
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(FindPersonEvent::FindByGuidEventName,$findPersonEvent);
        
        $person = $findPersonEvent->getPerson();
        
        if (!$person) 
        {
            $message = sprintf('No Person For User, %s %s',$user->getAccountName(),$user->getPersonGuid());
            throw new NotFoundHttpException($message);
        }
        $request->attributes->set('userPerson',$person);
    }
    /* ====================================================================
     * Copied from the game listener
     * TODO: replace with User events
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
