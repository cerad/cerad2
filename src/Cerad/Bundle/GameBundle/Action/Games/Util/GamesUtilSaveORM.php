<?php

namespace Cerad\Bundle\GameBundle\Action\Games\Util;

class GamesUtilSaveORMResults
{
    public $commit = false;
    
    public $total    = 0;
    public $created  = 0;
    public $updated  = 0;
    public $deleted  = 0;
}
class GamesUtilSaveORM
{
    protected $results;
    
    protected $gameRepo;
    protected $teamRepo;
    
    public function __construct($gameRepo,$teamRepo)
    {
        $this->gameRepo = $gameRepo;
        $this->teamRepo = $teamRepo;
    }
    protected function saveGame($gamex)
    {
        $num = (int)$gamex['num'];
        if (!$num) return;
        
        $levelKey   = $gamex['levelKey'];
        $projectKey = $gamex['projectKey'];
        
        // Delete for negative nums
        if ($num < 0)
        {
            $num *= -1;
            $game = $this->gameRepo->findOneByProjectNum($projectKey,$num);
            if ($game)
            {
                $this->gameRepo->remove($game);
                $this->results->deleted++;
            }
            return;
        }
        $game = $this->gameRepo->findOneByProjectNum($projectKey,$num);
        if (!$game)
        {// die('new game . ' . $num);
            $game = $this->gameRepo->createGame();
            $game->setNum($num);
            $game->setStatus('Active');
            $game->setProjectKey($projectKey);
            
            // TODO: Add officials?
            $slot = 1;
            foreach(array('Referee','AR1','AR2') as $role)
            {
                $gameOfficial = $game->createGameOfficial();
                $gameOfficial->setSlot($slot++);
                $gameOfficial->setRole($role);
                $gameOfficial->setAssignRole('ROLE_USER');
                $gameOfficial->setAssignState('Open');
                
                $game->addOfficial($gameOfficial);
            }
            $this->gameRepo->save($game);
            $this->results->created++;
        }
        $game->setDtBeg(new \DateTime($gamex['dtBeg']));
        $game->setDtEnd(new \DateTime($gamex['dtEnd']));
        
        $game->setFieldName($gamex['fieldName']);
        $game->setVenueName($gamex['venueName']);
        
        // Do I really want these to be changed?
        $game->setLevelKey ($levelKey);
        $game->setGroupType($gamex['groupType']);
        $game->setGroupName($gamex['groupName']);
        
        foreach($gamex['gameTeams'] as $gameTeamx)
        {
            $gameTeam = $game->getTeamForSlot($gameTeamx['slot']);
            $gameTeam->setName     ($gameTeamx['name']);
            $gameTeam->setLevelKey ($levelKey);
            $gameTeam->setGroupSlot($gameTeamx['groupSlot']);
            
            // Can we link?
            $team = $this->teamRepo->findOneByProjectLevelName($projectKey,$levelKey,$gameTeamx['name']);
            $teamNum = $team ? $team->getNum(): null;
          //$gameTeam->setTeamNum($teamNum);
        }      
        return; // This should not be updating referees
        
        // Optional Officials
        $gameOfficials = isset($gamex['officials']) ? $gamex['officials'] : array();
print_r($gameOfficials); die();        
        $gameOfficialSlot = 0;
        foreach($gameOfficials as $gameOfficial)
        {
            $slot = $gameOfficial['slot'];
            $name = $gameOfficial['name'];
            
            $official = $game->getOfficialForSlot($gameOfficialSlot);
            if (!$official)
            {
                $official = $game->createGameOfficial();
                $official->setSlot($gameOfficialSlot);
                $official->setRole($gameOfficialRole);
                $official->setPersonNameFull($gameOfficialName);
                $official->setAssignState('Open');
               
                switch($game->getGroupType())
                {
                    case 'PP':
                    case 'VIP':
                    case 'SOF':
                        $official->setAssignRole('ROLE_USER');
                        break;
                    case 'QF':
                    case 'SF':
                    case 'FM':
                        $official->setAssignRole('ROLE_ASSIGNOR');
                        break;
                    default:
                        die('No assign role for group type ' . $game->getGroupType());
                }
                $game->addOfficial($official);
            }
        }
    }
    /* ==============================================================
     * Main entry point
     */
    public function save($games,$commit = false)
    {
        $this->results = $results = new GamesUtilSaveORMResults();
        
        $results->commit = $commit;
        $results->total = count($games);
        
        foreach($games as $game)
        {
            $this->saveGame($game);
        }
        if ($results->commit) 
        {
            $this->gameRepo->commit();
        }
        return $results;
    }
}