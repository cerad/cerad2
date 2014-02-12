<?php
namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficial\AssignByUser;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\Security\Exception\AccessDeniedException;

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
        $this->workflow->process($this->gameOfficial,$this->projectOfficial);
        $this->gameRepo->commit();
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
            throw new AccessDeniedException(sprintf('Game Slot %d, %id is not user assignable.',$game->getNum(),$gameOfficial->getSlot()));
        }
        // Must be in the project, the commit checks for permissions
        $userPersonPlan = $userPerson->getPlanByProject($project);
        if (!$userPersonPlan)
        {
            throw new AccessDeniedException(sprintf('Game Slot %d, %id user is not in project.',$game->getNum(),$gameOfficial->getSlot()));
        }
        $this->projectOfficial = $userPersonPlan;
       
        // Adjust the official
        $gameOfficial->saveOriginalInfo();
        if (!$gameOfficial->getPersonNameFull())
        {
            $gameOfficial->setPersonNameFull($userPersonPlan->getPersonName());
        }
        // I am a factory
        return $this;
    }
}
