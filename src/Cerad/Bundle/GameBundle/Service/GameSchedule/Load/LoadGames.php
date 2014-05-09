<?php

namespace Cerad\Bundle\GameBundle\Service\GameSchedule\Load;

class LoadGames
{
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo = $gameRepo;
    }
    protected function processGame($gamex)
    {
        $num = (int)$gamex['num'];
        if (!$num) return;
        
        $levelKey   = $gamex['levelKey'];
        $projectKey = $gamex['projectKey'];
        
        $game = $this->gameRepo->findOneByProjectNum($projectKey,$num);
        if (!$game)
        {
            $game = $this->gameRepo->createGame();
            $game->setNum($num);
            $game->setStatus('Active');
            $game->setProjectKey($projectKey);
            
            $this->gameRepo->save($game);
        }
        $game->setDtBeg(new \DateTime($gamex['dtBeg']));
        $game->setDtEnd(new \DateTime($gamex['dtEnd']));
        
        $game->setGroupKey ($gamex['groupKey']);
        $game->setGroupType($gamex['groupType']);
        $game->setLevelKey ($levelKey);
        $game->setFieldName($gamex['fieldName']);
        $game->setVenueName($gamex['venueName']);
        
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
      //$this->gameRepo->commit();
    }
    public function process($games)
    {
        echo sprintf("Loading games %d\n",count($games));
        foreach($games as $game)
        {
            $this->processGame($game);
        }
        $this->gameRepo->commit();
    }
}