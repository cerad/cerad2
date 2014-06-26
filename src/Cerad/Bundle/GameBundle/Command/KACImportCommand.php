<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class KACImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__import_kac');
        $this->setDescription('Fix up assign roles');
        $this->addArgument   ('token', InputArgument::REQUIRED, 'Token');
        $this->addArgument   ('file',  InputArgument::REQUIRED, 'File');
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
        
      //$this->clearKACAssignments($gameRepo,$projectKey);
        
      //$this->setKACAssignRoles($gameRepo,$projectKey);
        
        $file = $input->getArgument('file');
        
        $games = $this->readKACGames($projectKey,$file);
        
        $this->saveKACGames($gameRepo,$games);
        
      //$this->enableU16Signups($gameRepo,$projectKey);
        
        return; if ($output);
    }
    protected function saveKACGames($gameRepo,$items)
    {
        foreach($items as $item)
        {
            $num = $item['num'];
            $projectKey = $item['projectKey'];
            $game = $gameRepo->findOneByProjectNum($projectKey,$num);
            if (!$game)
            {
                print_r($item); die();
            }
            foreach($item['officials'] as $official)
            {
                $gameOfficial = $game->getOfficialForSlot($official['slot']);
                $gameOfficial->changePerson(null);
                $gameOfficial->setPersonNameFull($official['personNameFull']);
                $gameOfficial->setAssignState('Pending');
                $gameOfficial->setAssignRole ('ROLE_ASSIGNOR_KAC');
            }
        }
        $gameRepo->flush();
    }
    protected function readKACGames($projectKey,$file)
    {
       $reader = $this->getService('cerad_game__games__util_read_zayso_xls');
       $games = $reader->read($file,$projectKey);
        
        echo sprintf("Games : %d\n",count($games));
        
        file_put_contents($file . '.yml',Yaml::dump($games,10)); 
        
        $gamesx = array();
        foreach($games as $game)
        {
            $added = false;
            foreach($game['officials'] as $official)
            {
                $name = $official['personNameFull'];
                if (substr($name,0,3) == 'KAC')
                {
                    if (!$added) $gamesx[] = $game;
                    $added = true;
                }
            }
        }
        echo sprintf("Gamesx: %d\n",count($gamesx));
        return $gamesx;
    }
    protected function enableU16Signups($gameRepo,$projectKey)
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
                if ($official->getAssignRole() == 'ROLE_DISABLED')
                {
                    $official->setAssignRole('ROLE_USER');
                }
            }
        }
        echo sprintf("U16 Game Count %d\n",count($games));
        $gameRepo->flush();
    }
    protected function clearKACAssignments($gameRepo,$projectKey)
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
                if (strpos($name,'KAC') !== false || 
                    $name == 'Standby' || 
                    $official->getAssignRole() == 'ROLE_ASSIGNOR_KAC')
                {
                    $official->setAssignRole('ROLE_USER');
                    $official->setAssignState('Open');
                    $official->changePerson(null);
                    $count++;
                }
            }
        }
        echo sprintf("KAC Count %d\n",$count);
        $gameRepo->flush();
        
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
}
?>
