<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class GamesImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__games__import');
        $this->setDescription('Import Games');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'File');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectRepo = $this->getService  ('cerad_project.project_repository');
        $projectKey  = $this->getParameter('cerad_project_project_default');
        $project = $projectRepo->find($projectKey);
        
        $file = $input->getArgument('file');
        
        $this->importGames($project,$file); 
        
        return; if ($output);
    }
    protected function importGames($project,$file)
    {
        $reader = $this->getService('cerad_game__games__util_read_zayso_xls');
        
        $games = $reader->read($file,$project);
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents($file . '.yml',Yaml::dump($games,10));
return;        
        $saver = $this->getService('cerad_game__games__util_save_orm');
        $saveResults = $saver->save($games,true);
        $saveResults->basename = $file;
        print_r($saveResults);
                
        return;        
    }
}
?>
