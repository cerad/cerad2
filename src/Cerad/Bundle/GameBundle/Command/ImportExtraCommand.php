<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

/* =========================================================
 * Reloads all extra games from ground zero
 */
class ImportExtraCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__import_extra');
        $this->setDescription('Extra Import');
      //$this->addArgument   ('token', InputArgument::REQUIRED, 'Token');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected $extraGamesFile = 'data/ScheduleExtra20140625.txt';
    protected $extraTeamsFile = 'data/TeamsExtra20140620.xlsx';
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      //$gameRepo = $this->getService('cerad_game__game_repository');
        
        $projectKey = 'AYSONationalGames2014';
                
        $this->importExtraGames($projectKey);
        $this->importExtraTeams($projectKey);
        $this->linkExtraTeams  ($projectKey);
                
        // Done
        return; if ($input && $output);
    }
    protected function linkExtraTeams($projectKey)
    {
        $file = $this->extraTeamsFile;
         
        $reader = $this->getService('cerad_game__project__teams__reader_zayso');
         
        $teams = $reader->read($projectKey,$file);
        
        echo sprintf("Link Extra Teams: %d\n",count($teams));
        
        file_put_contents($file . '.yml',Yaml::dump($teams,10));

        $saver = $this->getService('cerad_game__project__teams__saver_zayso');

        // 2 = Link only
        $results = $saver->save($teams,true,2);
        $results->basename = $file;
        
        print_r($results);           
    }
    protected function importExtraTeams($projectKey)
    {
        $file = $this->extraTeamsFile;
         
        // Cerad\Bundle\GameBundle\Action\Project\Teams\Reader\TeamsReaderZayso
        $reader = $this->getService('cerad_game__project__teams__reader_zayso');
         
        $teams = $reader->read($projectKey,$file);
        
        echo sprintf("Link Extra Teams: %d\n",count($teams));
        
        file_put_contents($file . '.yml',Yaml::dump($teams,10));

        // Cerad\Bundle\GameBundle\Action\Project\Teams\Saver\TeamsSaverZayso
        $saver = $this->getService('cerad_game__project__teams__saver_zayso');

        // 1 = Team Only
        $results = $saver->save($teams,true,1);
        $results->basename = $file;
        
        print_r($results);           
    }
    protected function importExtraGames($projectKey)
    {
        $file = $this->extraGamesFile;
        
        //Cerad\Bundle\GameBundle\Action\Games\Reader\GamesReaderNG2014
        $reader = $this->getService('cerad_game__games__reader_ng2014');
         
        $games = $reader->read($projectKey,$file);
        
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
