<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

/* =========================================================
 * Lost U14 fields RU4 and RU5.
 * Took WP1 and WP2 from VIP
 * Resuffled all U16 games.
 */
class U16CoreSoccerfestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__u16_core_soccerfest');
        $this->setDescription('U16 Core Soccerfest');
      //$this->addArgument   ('file', InputArgument::REQUIRED, 'file');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected $refereeFile = 'data/U16SoccerfestUpdate/U16SoccerfestReferees.xlsx';
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gameRepo = $this->getService('cerad_game__game_repository');
        
        $projectKey = 'AYSONationalGames2014';
        
        $file = $this->refereeFile; //$input->getArgument('file');
        
        $this->removeU16CoreSOF($gameRepo,$projectKey);
        
        $this->importU16CoreSOF($file,$projectKey);
        
        $this->importU16CoreSOFReferees($file,$projectKey);
        
        // Done
        return; if ($input && $output);
    }
    protected function importU16CoreSOFReferees($file,$projectKey)
    {   
        // Cerad\Bundle\GameBundle\Action\Games\Util\GamesUtilReadZaysoXLS
        $reader = $this->getService('cerad_game__games__util_read_zayso_xls');
         
        $games = $reader->read($file,$projectKey);
                
        file_put_contents($file . '.yml',Yaml::dump($games,10));
        
        $saver = $this->getService('cerad_game__project__game_officials__assign_by_import__save_orm');
        
        $saver->save($games,true,'Published',true); // commit state verify
    }
    protected function importU16CoreSOF($file,$projectKey)
    {   
        // Cerad\Bundle\GameBundle\Action\Games\Util\GamesUtilReadZaysoXLS
        $reader = $this->getService('cerad_game__games__util_read_zayso_xls');
         
        $games = $reader->read($file,$projectKey);
                
        file_put_contents($file . '.yml',Yaml::dump($games,10));

        // Cerad\Bundle\GameBundle\Action\Games\Saver\GamesSaverNG2014
        $saver = $this->getService('cerad_game__games__saver_ng2014');
        
        $results = $saver->save($games,true);
        $results->basename = $file;    
        
        print_r($results);
        
    }
    protected function removeU16CoreSOF($gameRepo,$projectKey)
    {
        $criteria = array(
            'groupTypes'    => array('SOF'),
            'levelKeys'     => array('AYSO_U16G_Core','AYSO_U16B_Core'),
            'projectKeys'   => $projectKey,
            'wantOfficials' => false,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        $count = 0;
        foreach($games as $game)
        {
            $gameRepo->remove($game);
            $count++;
        }
        echo sprintf("U16 %d Remove SOF Count %d\n",count($games),$count); // 192 144
        $gameRepo->flush();
    }
}
?>
