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
        $this->addArgument   ('type', InputArgument::REQUIRED, 'zayso or ng2014');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'file');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectRepo = $this->getService  ('cerad_project.project_repository');
        $projectKey  = $this->getParameter('cerad_project_project_default');
        $project = $projectRepo->find($projectKey);
        
        $type = $input->getArgument('type');
        $file = $input->getArgument('file');
        
        switch($type)
        {
            case 'zayso'  : $this->importGamesZayso ($project,$file); break;
            case 'ng2014' : $this->importGamesNG2014($project,$file); break;
        }
        return; if ($output);
    }
    protected function importGamesNG2014($project,$file)
    {   
        /* ======================================================
         * All teams in a matrix
         */
        $reader = $this->getService('cerad_game__games__reader_ng2014');
         
        $games = $reader->read($project,$file);
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents($file . '.yml',Yaml::dump($games,10));

        $saver = $this->getService('cerad_game__games__saver_zayso');
        
        $results = $saver->save($games,true);
        
        print_r($results);
    }
    protected function importGamesZayso($project,$file)
    {
        $reader = $this->getService('cerad_game__games__reader_zayso');
        
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
