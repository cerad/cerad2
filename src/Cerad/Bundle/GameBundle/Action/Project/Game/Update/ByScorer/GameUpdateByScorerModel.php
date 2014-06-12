<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Game\Update\ByScorer;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;


class GameUpdateByScorerModel extends ActionModelFactory
{
    // Request
    public $back;
    public $game;
    public $project;
    
    public $_game;
    public $_route;
    public $_project;
    public $_template;
    
    // Injected
    protected $gameRepo;
    protected $teamRepo;
    
    public function __construct($gameRepo,$teamRepo)
    {   
        $this->gameRepo = $gameRepo;
        $this->teamRepo = $teamRepo;
    }
    public function process()
    {       
        // Save
        $this->gameRepo->commit();
        return;
    }
    public function create(Request $request)
    { 
       // Extract
        $requestAttrs = $request->attributes;
        
        // These will be set or never get here
        $this->game     = $game    = $requestAttrs->get('game');
        $this->project  = $project = $requestAttrs->get('project');
        
        $this->_game     = $requestAttrs->get('_game');
        $this->_route    = $requestAttrs->get('_route');
        $this->_project  = $requestAttrs->get('_project');
        $this->_template = $requestAttrs->get('_template');
        
        $this->back = $request->query->get('back');
        
        // TODO: Make a form data object
        
        // Factory
        return $this;
    }
    public function findVenueFields()
    {
      //$levelKey   = $this->game->getLevelKey(); // Might be too restrictive
        $projectKey = $this->game->getProjectKey();
        return $this->gameRepo->queryVenues($projectKey);
    }
    public function findFieldNames()
    {
        $criteria = array(
          'projectKeys' => $this->game->getProjectKey(),  
        );
        return $this->gameRepo->queryFieldChoices($criteria);
    }
    public function findPhysicalTeams()
    { 
        $game = $this->game;
        return $this->teamRepo->findAllByProjectLevels($game->getProjectKey(),$game->getLevelKey());        
    }
}
