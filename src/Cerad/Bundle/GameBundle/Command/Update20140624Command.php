<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

/* =========================================================
 * Lost U14 fields RU4 and RU5.
 * Took WP1 and WP2 from VIP
 * Resuffled all U16 games.
 */
class Update20140624Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__update_24');
        $this->setDescription('Update 24');
      //$this->addArgument   ('token', InputArgument::REQUIRED, 'Token');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gameRepo = $this->getService('cerad_game__game_repository');
        
        $projectKey = 'AYSONationalGames2014';
        
        $this->removeVIPFromWP1_WP2($gameRepo,$projectKey);
        $this->addVIPReferees      ($gameRepo,$projectKey);
        
        $this->removeU16ExceptSOF  ($gameRepo,$projectKey);
        $this->changeU16SOFFields  ($gameRepo,$projectKey);
        
        $this->importU16Games($projectKey);
        $this->linkU16Teams  ($projectKey);
                
        // Done
        return; if ($input && $output);
    }
    protected function linkU16Teams($projectKey)
    {
        $file = 'data/update20140624/Teams20140624.xlsx';
         
        $reader = $this->getService('cerad_game__project__teams__reader_zayso');
         
        $teams = $reader->read($projectKey,$file);
        
        echo sprintf("U16 Teams: %d\n",count($teams));
        
        file_put_contents($file . '.yml',Yaml::dump($teams,10));

        $saver = $this->getService('cerad_game__project__teams__saver_zayso');

        // 2 = Link only
        $results = $saver->save($teams,true,2);
        $results->basename = $file;
        
        print_r($results);           
    }
    protected function importU16Games($projectKey)
    {
        $file = 'data/update20140624/ScheduleCoreUpdate20140624.txt';
        
        $reader = $this->getService('cerad_game__games__reader_ng2014');
         
        $games = $reader->read($projectKey,$file);
        
        echo sprintf("New U16 Games: %d\n",count($games)); // 144
        
        file_put_contents($file . '.yml',Yaml::dump($games,10));

        $saver = $this->getService('cerad_game__games__saver_zayso');
        
        $results = $saver->save($games,true);
        $results->basename = $file;
        
        print_r($results);
        
    }
    protected function removeVIPFromWP1_WP2($gameRepo,$projectKey)
    {
        $criteria = array(
            'levelKeys'     => array('AYSO_VIP_Core'),
            'projectKeys'   => $projectKey,
            'wantOfficials' => false,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        $count = 0;
        foreach($games as $game)
        {
            switch($game->getFieldName())
            {
                case 'WP1':
                case 'WP2':
                    $gameRepo->remove($game);
                    $count++;
                    break;
            }
        }
        echo sprintf("VIP %d Remove Count %d\n",count($games),$count); // 56
        $gameRepo->flush();
    }
    protected function addVIPReferees($gameRepo,$projectKey)
    {
        $criteria = array(
            'levelKeys'     => array('AYSO_VIP_Core'),
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        $count = 0;
        foreach($games as $game)
        {
            if (count($game->getOfficials()) != 3)
            {
                $count++;
                foreach(array(2 => 'AR1', 3 => 'AR2') as $slot => $role)
                {
                    $ar = $game->createGameOfficial();
                    $ar->setSlot($slot);
                    $ar->setRole($role);
                    $ar->setAssignRole('ROLE_USER');
                    $game->addOfficial($ar);
                }
            }
        }
        echo sprintf("VIP %d Add Referees Count %d\n",count($games),$count); // 56
        $gameRepo->flush();
    }
    protected function removeU16ExceptSOF($gameRepo,$projectKey)
    {
        $criteria = array(
            'levelKeys'     => array('AYSO_U16G_Core','AYSO_U16B_Core'),
            'projectKeys'   => $projectKey,
            'wantOfficials' => false,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        $count = 0;
        foreach($games as $game)
        {
            if ($game->getGroupType() != 'SOF')
            {
                $gameRepo->remove($game);
                $count++;
            }
        }
        echo sprintf("U16 %s Remove Count %d\n",count($games),$count); // 192 144
        $gameRepo->flush();
    }
    protected function changeU16SOFFields($gameRepo,$projectKey)
    {
        $criteria = array(
            'groupKeys'     => array('SOF'),
            'levelKeys'     => array('AYSO_U16G_Core','AYSO_U16B_Core'),
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        $count = 0;
        foreach($games as $game)
        {
            switch($game->getFieldName())
            {
                case 'RU4':
                    $game->setVenueName('Wilson Park');
                    $game->setFieldName('WP1');
                    $count++;
                    $this->resetGameOfficials($game,'ROLE_USER');
                    break;
                
                case 'RU5':
                    $game->setVenueName('Wilson Park');
                    $game->setFieldName('WP2');
                    $count++;
                    $this->resetGameOfficials($game,'ROLE_USER');
                    break;
            }
        }
        echo sprintf("U16 %s SOF Fields Count %d\n",count($games),$count); // 192 144
        $gameRepo->flush();
    }
    protected function resetGameOfficials($game,$assignRole)
    {
        foreach($game->getOfficials() as $gameOfficial)
        {
            $gameOfficial->changePerson  (null);
            $gameOfficial->setAssignRole ($assignRole);
            $gameOfficial->setAssignState('Open');
        }
    }
}
?>
