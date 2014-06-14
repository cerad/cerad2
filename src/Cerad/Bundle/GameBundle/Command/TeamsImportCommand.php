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
        
      //$this->processTeams($project,$file); 
        
        $this->processTeamsEayso($project,$file); 
        
        return; if ($output);
    }
    protected function processTeamsEayso($project,$file)
    {
        $reader = $this->getService('cerad_game__project__teams__reader_eayso');
        
        $teams = $reader->read($project,$file);
        
        echo sprintf("Teams: %d\n",count($teams));
        
        file_put_contents($file . '.yml',Yaml::dump($teams,10));
        
    }
    protected function processTeams($project,$file)
    {
        $readTeams = $this->getService('cerad_game__project__teams__util_read_xls');
        
        $teams = $readTeams->read($project,$file);
        
        echo sprintf("Teams: %d\n",count($teams));
        
        file_put_contents($file . '.yml',Yaml::dump($teams,10));
        
        $saveTeams = $this->getService('cerad_game__project__teams__util_save_orm');
        $saveResults = $saveTeams->save($teams,true);
        print_r($saveResults);
        
        $linkTeams = $this->getService('cerad_game__project__teams__util_link_orm');
        $linkResults = $linkTeams->link($teams,true);
        print_r($linkResults);
        
        return;        
    }
}
?>
