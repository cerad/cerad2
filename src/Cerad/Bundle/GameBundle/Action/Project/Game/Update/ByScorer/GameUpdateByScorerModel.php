<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Game\Update\ByScorer;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GameUpdateByScorerModel extends ActionModelFactory
{
    // Request
    public $game;
    public $project;
    public $_project;
    
    // Generated
    public $gameClone;
    public $gameTeamHomeClone;
    public $gameTeamAwayClone;
    
    // Injected
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {   
        $this->gameRepo = $gameRepo;
    }
    
    /* =====================================================
     * Process a posted model
     * Turn everything over to the workflow
     */
    public function process()
    {   
        // In time
      //$this->workflow->process($this->project,$this->game,$this->gameClone);
        
        // Two ways to update team names
        $this->processTeamName($this->game->getHomeTeam(),$this->gameTeamHomeClone);
        $this->processTeamName($this->game->getAwayTeam(),$this->gameTeamAwayClone);
        
        // Save
        $this->gameRepo->commit();
        return;
    }
    protected function processTeamName($team,$teamClone)
    {
        // Used select list
        if ($team->getName() != $teamClone->getName()) return;
        
        // Used text box
        if ($team->getName() == $team->namex) return;
        
        $name = trim($team->namex);
        if (!$name) return $name;
        
        $team->setName($name);
    }
    /* =========================================================================
     * TODO: Do the variable name matching and inject project/game directly
     */
    public function create(Request $request)
    { 
       // Extract
        $requestAttrs = $request->attributes;
        
        // These will be set or never get here
        $this->game     = $game    = $requestAttrs->get('game');
        $this->project  = $project = $requestAttrs->get('project');
        $this->_project = $request->attributes->get('_project');
        
        $this->back = $request->query->get('back');
       
        // Want to allow updating name by either select or text
        $homeTeam = $game->getHomeTeam();
        $awayTeam = $game->getAwayTeam();
        
        // Need to override game clonner to get teams and officials
        // Might be better to store the clone in the game itself $game->createClone();
        $this->gameClone = clone $game;
        $this->gameTeamHomeClone = clone $homeTeam;
        $this->gameTeamAwayClone = clone $awayTeam;
        
        // Factory
        return $this;
    }
    public function getGameFields()
    {
        return array();
        return $this->gameFieldRepo->findByProject($this->project);
    }
    public function getTeamNameChoices()
    {
        $criteria = array
        (
            'levelKeys'   => $this->game->getLevelKey(),
            'projectKeys' => $this->project->getKey()
        );
        return $this->gameRepo->queryTeamChoices($criteria);
    }
}
