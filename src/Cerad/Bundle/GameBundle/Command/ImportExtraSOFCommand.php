<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

/* =========================================================
 * Reloads all extra games from ground zero
 */
class ImportExtraSOFCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__import_extra_sof');
        $this->setDescription('Extra SOF Import');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'File');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $projectKey = 'AYSONationalGames2014';
                
        $file = $input->getArgument('file');
        
        $this->importExtraSOF($file,$projectKey);
        
        $this->setUserAssignRoles($projectKey);
        
        // Done
        return; if ($input && $output);
    }
    protected function setUserAssignRoles($projectKey)
    {
        $gameRepo = $this->getService('cerad_game__game_repository');
       
        $groupTypes = array('SOF');
        
        $criteria = array(
            'groupTypes'    => $groupTypes,
            'projectKeys'   => $projectKey,
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        $count = 0;
        
        foreach($games as $game)
        {
            $levelKey = $game->getLevelKey();
            if (strpos($levelKey,'Extra'))
            {
                foreach($game->getOfficials() as $gameOfficial)
                {
                    $gameOfficial->setAssignRole('ROLE_USER');
                    $count++;
                }
            }
        }
        $gameRepo->flush();        
        echo sprintf("Extra SOF Roles Count %d\n",$count);
    }
    protected function importExtraSOF($file,$projectKey)
    {   
        // Cerad\Bundle\GameBundle\Action\Games\Util\GamesUtilReadZaysoXLS
        $reader = $this->getService('cerad_game__games__util_read_zayso_xls');
         
        $games = $reader->read($file,$projectKey);
        
        echo sprintf("Extra Games: %d\n",count($games)); // 144
        
        file_put_contents($file . '.yml',Yaml::dump($games,10));

        // Cerad\Bundle\GameBundle\Action\Games\Saver\GamesSaverNG2014
        $saver = $this->getService('cerad_game__games__saver_ng2014');
        
        $results = $saver->save($games,true);
        $results->basename = $file;    
        
        print_r($results);
        
    }
}
?>
