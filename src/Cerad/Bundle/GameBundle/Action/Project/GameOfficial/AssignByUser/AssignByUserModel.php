<?php
namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficial\AssignByUser;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Cerad\Bundle\GameBundle\Action\Project\GameOfficials\Assign\AssignWorkflow;

/* =======================================================
 * This model has dependencies from different bundles
 * Good argument for leaving it in the tourn bundle?
 */
class AssignByUserModel
{
    public $project;
    public $workflow;
    
    public $game;
    public $gameOfficial;
    public $gameOfficialClone;
        
    public $projectOfficial; // The current user's project plan
    
    protected $gameRepo;
    protected $dispatcher;
    
    public function __construct(AssignWorkflow $workflow, $gameRepo)
    {   
        $this->workflow = $workflow;
        $this->gameRepo = $gameRepo;
    }
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    /* =====================================================
     * Process a posted model
     * Turn everything over to the workflow
     */
    public function process()
    {   
        $changed = $this->workflow->process(
            $this->project,
            $this->gameOfficialClone,
            $this->gameOfficial,
            $this->projectOfficial
        );
        if ($changed)
        {
            $this->gameRepo->commit();
        }
        return;
    }
    /* =========================================================================
     * Also holds logic to allow signing up for this particular game slot?
     */
    public function create(Request $request)
    { 
       // Extract
        $requestAttrs = $request->attributes;
        
        // These will be set or never get here
        $this->project      = $project      = $requestAttrs->get('project');
        $this->game         = $game         = $requestAttrs->get('game');
        $this->gameOfficial = $gameOfficial = $requestAttrs->get('gameOfficial');
        $this->userPerson   = $userPerson   = $requestAttrs->get('userPerson');
        
        if (!$gameOfficial->isAssignableByUser()) 
        {
          //throw new AccessDeniedException(sprintf('Game Slot %d, %d is not user assignable.',$game->getNum(),$gameOfficial->getSlot()));
        }
        // Must be in the project, the commit checks for permissions
        $userPersonPlan = $userPerson->getPlanByProject($project);
        if (!$userPersonPlan)
        {
            throw new AccessDeniedException(sprintf('Game Slot %d, %d user is not in project.',$game->getNum(),$gameOfficial->getSlot()));
        }
        $this->projectOfficial = $userPersonPlan;
       
        // Adjust the official
        $this->gameOfficialClone = clone $gameOfficial;
        if (!$gameOfficial->getPersonNameFull())
        {
            $gameOfficial->setPersonNameFull($userPersonPlan->getPersonName());
        }
        // I am a factory
        return $this;
    }
}
