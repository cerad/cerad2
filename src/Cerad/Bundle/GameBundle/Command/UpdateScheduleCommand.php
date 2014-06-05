<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class UpdateScheduleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__update_schedule');
        $this->setDescription('Update Game Schedule');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'Schedule');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // The request listener is of course not requesting
        $projectRepo = $this->getService  ('cerad_project.project_repository');
        $projectKey  = $this->getParameter('cerad_project_project_default');
        $project = $projectRepo->find($projectKey);
        
        $file = $input->getArgument('file');
        
        $this->processGames($project,$file); 
        
        return; if ($output);
    }
    protected function processGames($project,$file)
    {
        $readGames = $this->getService('cerad_game__project__schedule_game__import_read_xls');
        
        $games = $readGames->read($project,$file);
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents('data/Games.yml',Yaml::dump($games,10));

        return;
        
        $loader = $this->getService('cerad_game__load_games');
        $loader->process($games);
        
        return;        
    }
}
?>
