<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Util;

class Results
{
    public $commit = false;
    
    public $basename;
    
    public $total   = 0;
    public $created = 0;
    public $updated = 0;
    public $deleted = 0;
}
class ScheduleGameUtilSaveORM
{
    protected $results;
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo = $gameRepo;
    }
    /* ====================================================
     * Save a game
     */
    protected function saveGame($gamex)
    {
        $results = $this->results;
        
        $num        = (int)$gamex['num'];
        $levelKey   = $gamex['levelKey'];
        $projectKey = $gamex['projectKey'];
        
        $game = $this->gameRepo->findOneByProjectNum($projectKey,$num);
        
        if (!$game)
        {
            $game = $this->gameRepo->createGame();
            $game->setNum($num);
            $game->setStatus('Active');
            $game->setLevelKey  ($levelKey);
            $game->setProjectKey($projectKey);
            
            $this->gameRepo->save($game);
            
            $results->created++;
        }
        $game->setDtBeg(new \DateTime($gamex['dtBeg']));
        $game->setDtEnd(new \DateTime($gamex['dtEnd']));
        
        $game->setFieldName($gamex['fieldName']);
        $game->setVenueName($gamex['venueName']);
        
        $game->setGroupType($gamex['groupType']);
        $game->setGroupName($gamex['groupName']);
        
        $homeTeam = $game->getHomeTeam();
        $homeTeam->setName     ($gamex['homeTeamName']);
        $homeTeam->setGroupSlot($gamex['homeTeamGroupSlot']);
        $homeTeam->setLevelKey ($levelKey);
        
        $awayTeam = $game->getAwayTeam();
        $awayTeam->setName     ($gamex['awayTeamName']);
        $awayTeam->setGroupSlot($gamex['awayTeamGroupSlot']);
        $awayTeam->setLevelKey ($levelKey);
        
        $gameOfficials = $gamex['officials'];
        $gameOfficialSlot = 0;
        foreach($gameOfficials as $gameOfficialRole => $gameOfficialName)
        {
            $gameOfficialSlot++;
            $official = $game->getOfficialForSlot($gameOfficialSlot);
            if (!$official)
            {
                $official = $game->createGameOfficial();
                $official->setSlot($gameOfficialSlot);
                $official->setRole($gameOfficialRole);
                $official->setPersonNameFull($gameOfficialName);
                $official->setAssignState('Open');
               
                if ($game->getGroupType() == 'PP')
                {
                    $official->setAssignRole('ROLE_USER');
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
        $this->results = $results = new Results();
        
        $results->commit = $commit;
        $results->total = count($games);
        
        foreach($games as $game)
        {
            $this->saveGame($game);
        }
         
        if ($results->commit) $this->gameRepo->commit();
        
        return $results;
    }
}
?>
