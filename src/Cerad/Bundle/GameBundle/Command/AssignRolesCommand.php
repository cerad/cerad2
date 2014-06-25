<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssignRolesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__assign_roles');
        $this->setDescription('Fix up assign roles');
        $this->addArgument   ('token', InputArgument::REQUIRED, 'Token');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Little security
        $token = $input->getArgument('token');
        if ($token != 894) return;
        
        $gameRepo = $this->getService('cerad_game__game_repository');
        
        $projectKey = 'AYSONationalGames2014';
        
      //$this->setMedalRoundAssignStates($gameRepo,$projectKey);
      //
      //$this->setKACAssignRoles($gameRepo,$projectKey);
      //$this->setSoccerFestAssignRoles($gameRepo,$projectKey);
      //$this->setVIPAssignRoles($gameRepo,$projectKey);
      //
      //$this->setRedondoAssignRoles($gameRepo,$projectKey);
                     
        $this->disableU16Signups($gameRepo,$projectKey);
        
        return; if ($output);
    }
    protected function setKACAssignRoles($gameRepo,$projectKey)
    {
       $groupTypes = array('PP');
        
        $criteria = array(
            'groupTypes'    => $groupTypes,
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        $count = 0;
        foreach($games as $game)
        {
            foreach($game->getOfficials() as $official)
            {
                $name = $official->getPersonName();
                if (strpos($name,'KAC') !== false || $name == 'Standby')
                {
                    $official->setAssignRole('ROLE_ASSIGNOR_KAC');
                    $count++;
                }
            }
        }
        echo sprintf("KAC Count %d\n",$count);
        $gameRepo->flush();
        
    }
    protected function setSoccerFestAssignRoles($gameRepo,$projectKey)
    {
       $groupTypes = array('SOF');
        
        $criteria = array(
            'groupTypes'    => $groupTypes,
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        foreach($games as $game)
        {
            foreach($game->getOfficials() as $official)
            {
                $official->setAssignRole('ROLE_USER');
            }
        }
        echo sprintf("SOF Game Count %d\n",count($games));
        $gameRepo->flush();
        
    }
    protected function setRedondoAssignRoles($gameRepo,$projectKey)
    {
        $criteria = array(
          //'levelKeys'     => array('AYSO_U16G_Core'),
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        $count = 0;
        foreach($games as $game)
        {
            if ($game->getVenueName() == 'Redondo Union HS')
            {
                $count++;
                foreach($game->getOfficials() as $official)
                {
                    $official->setAssignRole('ROLE_DISABLED');
                }
            }
        }
        echo sprintf("Redondo HS Game Count %d\n",$count);
        $gameRepo->flush();
    }
    protected function disableU16Signups($gameRepo,$projectKey)
    {
        $criteria = array(
            'levelKeys'     => array('AYSO_U16G_Core','AYSO_U16B_Core'),
            'groupTypes'    => array('SOF','PP'),
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        foreach($games as $game)
        {
            foreach($game->getOfficials() as $official)
            {
                $official->setAssignRole('ROLE_DISABLED');
            }
        }
        echo sprintf("U16 Game Count %d\n",count($games));
        $gameRepo->flush();
    }
    protected function setVIPAssignRoles($gameRepo,$projectKey)
    {
        $levelKeys = array('AYSO_VIP_Core','AYSO_VIP_Extra');
        
        $criteria = array(
            'levelKeys'     => $levelKeys,
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        echo sprintf("VIP Game Count %d\n",count($games));
       
        foreach($games as $game)
        {
            $game->setGroupType('VIP');
            
            foreach($game->getOfficials() as $official)
            {
                $official->setAssignRole('ROLE_USER');
            }
        }
        $gameRepo->flush();        
    }
    protected function setMedalRoundAssignRoles($gameRepo,$projectKey)
    {
        $groupTypes = array('QF','SF','FM');
        
        $criteria = array(
            'groupTypes'    => $groupTypes,
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        echo sprintf("Medal Round Game Count %d\n",count($games));
        
        foreach($games as $game)
        {
            foreach($game->getOfficials() as $gameOfficial)
            {
                $gameOfficial->setAssignRole('ROLE_ASSIGNOR');
            }
        }
        $gameRepo->flush();        
    }
}
?>
