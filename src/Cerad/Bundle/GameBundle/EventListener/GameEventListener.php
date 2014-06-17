<?php
namespace Cerad\Bundle\GameBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Cerad\Bundle\GameBundle\GameEvents;
use Cerad\Bundle\GameBundle\Event\GameOfficial\AssignSlotEvent;
use Cerad\Bundle\GameBundle\Event\FindResultsEvent;

use Cerad\Bundle\CoreBundle\Event\Team\ChangedTeamEvent;
use Cerad\Bundle\CoreBundle\Event\Game\UpdatedGameReportEvent;

use Cerad\Bundle\CoreBundle\Event\FindProjectTeamsEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectLevelsEvent;

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
            
            FindResultsEvent::EventName  => array('onFindResults'),
            
            FindProjectTeamsEvent::FindProjectTeams  => array('onFindProjectTeams'),
            
            ChangedTeamEvent::Changed  => array('onChangedTeam'),
            
            UpdatedGameReportEvent::Updated  => array('onUpdatedGameReport'),
            
            GameEvents::GameOfficialAssignSlot  => array('onGameOfficialAssignSlot'),
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
    protected function getGameTeamRepository()
    {
        return $this->container->get('cerad_game__game_team_repository');
    }
    /* ==============================================
     * 27 June 2014
     * Getting quite a few spurious game numbers
     * They often have 4 or 6 digits instead of the final standard of 5
     * From old saved links?  Strange because the numbers themselves change
     * 
     * So exception either needs to be handled or not thrown at all
     */
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
            $_route = $request->attributes->get('_route');
            if ($_route != 'cerad_game__project__game_report__update')
            {
                throw new NotFoundHttpException(sprintf('Game %s %d not found',$projectKey,$gameNum));
            }
        }
        // Stash It
        $request->attributes->set('game',$game);
        
        // Check for game official
        if ($request->attributes->has('_gameOfficial')) 
        {
            $slot = $request->attributes->get('_gameOfficial');
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
    }
    /* ====================================================================
     * Finds the teams for a given project filtering by assorted other queries
     */
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
        
        $teamRepo = $this->container->get('cerad_game__team_repository');
        $teams = $teamRepo->findAllByProjectLevels($project,$levelKeys);
        
        $event->setTeams($teams);
        $event->stopPropagation();
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
    /* ========================================================
     * 14 June 2014
     * First shot at advancing medal round teams
     * This is the sort of thing that should be passed to a service
     */
    public function onUpdatedGameReport(UpdatedGameReportEvent $event)
    {
        $game = $event->getGame();
        
        switch($game->getReportStatus())
        {
            case 'Verified': break;
            default: return;
        }
        switch($game->getGroupType())
        {
            case 'QF': case 'SF': break;
            default: return;
        }
        $teamResults = $game->getTeamResults();
        
        // Should not happen
        if (!$teamResults) return;
        
        // Advance
        $this->advanceTeam($game,$teamResults['winner'],'Win');
        $this->advanceTeam($game,$teamResults['loser' ],'Run');
    }
    protected function advanceTeam($game,$gameTeam,$groupSlotType)
    {       
        $groupSlot = sprintf('%s%s %s',$game->getGroupType(),$game->getGroupName(),$groupSlotType);
        
        $gameTeamRepo = $this->container->get('cerad_game__game_team_repository');
        
        $gameTeamNext = $gameTeamRepo->findOneByProjectLevelGroupSlot(
            $game->getProjectKey(),
            $game->getLevelKey(),
            $groupSlot);
        
        if (!$gameTeamNext) return;
        
        if ($gameTeamNext->hasTeam()) return;
        
        $teamRepo = $this->container->get('cerad_game__team_repository');
        $team = $teamRepo->findOneByKey($gameTeam->getTeamKey());
        $gameTeamNext->setTeam($team);
    }
    public function onChangedTeam(ChangedTeamEvent $event)
    {
        $team      = $event->getTeam();
        $groupSlot = $event->getGroupSlot();
        
        $gameTeamRepo = $this->getGameTeamRepository();
        
        // Deal with team name cnd points changes
        $gameTeams = $gameTeamRepo->findAllByTeamKey($team->getKey());
       
        foreach($gameTeams as $gameTeam)
        {
            $gameTeam->setTeam($team);
        }
        
        // Different processing for setting the slot
        if (!$groupSlot) return;
        
        $gameTeamsGS = $gameTeamRepo->findAllByProjectLevelGroupSlot(
            $team->getProjectKey(),
            $team->getLevelKey(),
            $groupSlot
        );
        foreach($gameTeamsGS as $gameTeam)
        {
            $gameTeam->setTeam($team);
        }
        return;
        
        echo sprintf("%s %s %d\n",$team->getName(),$groupSlot,count($gameTeams));
        die();
    }
}
?>
