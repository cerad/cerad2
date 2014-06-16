<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class TeamsImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__teams__import');
        $this->setDescription('Import Teams');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'Team');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectRepo = $this->getService  ('cerad_project.project_repository');
        $projectKey  = $this->getParameter('cerad_project_project_default');
        $project = $projectRepo->find($projectKey);
        
        $file = $input->getArgument('file');
        
      //$this->processTeamsAll($project,$file); 
        
      //$this->processTeamsEayso($project,$file); 
        
        $this->processTeamsZayso($project,$file); 
        
      //$this->syncTeams($project); 
        
        return; if ($output);
    }
    protected function syncTeams($project)
    {   
        $syncer = $this->getService('cerad_game__project__game_team__syncer');
        
        $results = $syncer->sync($project,true);
        
        print_r($results);
    }
    protected function processTeamsAll($project,$file)
    {   
        /* ======================================================
         * All teams in a matrix
         */
        $allReader = $this->getService('cerad_game__project__teams__reader_all');
         
        $allTeams = $allReader->read($project,$file,'NG All');
        
        echo sprintf("All   Teams: %d\n",count($allTeams));
        
        file_put_contents($file . '.all.yml',Yaml::dump($allTeams,10));

        $allSaver = $this->getService('cerad_game__project__teams__saver_all');
        
        $allSaverResults = $allSaver->save($allTeams,true);
        
        print_r($allSaverResults);
    }
    protected function processTeamsEayso($project,$file)
    {   
        /* ======================================================
         * All teams in a matrix
         */
        $reader = $this->getService('cerad_game__project__teams__reader_eayso');
         
        $teams = $reader->read($project,$file);
        
        echo sprintf("Eayso Teams: %d\n",count($teams));
        
        file_put_contents($file . '.eayso.yml',Yaml::dump($teams,10));

        $saver = $this->getService('cerad_game__project__teams__saver_eayso');

        $results = $saver->save($teams,true);
        
        print_r($results);   
    }
    protected function processTeamsZayso($project,$file)
    {   
        /* ======================================================
         * All teams in a matrix
         */
        $reader = $this->getService('cerad_game__project__teams__reader_zayso');
         
        $teams = $reader->read($project,$file);
        
        echo sprintf("Zayso Teams: %d\n",count($teams));
        
        file_put_contents($file . '.yml',Yaml::dump($teams,10));

        $saver = $this->getService('cerad_game__project__teams__saver_zayso');

        $results = $saver->save($teams,true);
        
        print_r($results);   
    }
}
?>
