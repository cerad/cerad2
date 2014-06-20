<?php

namespace Cerad\Bundle\PersonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class PersonFedsImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_person__person_feds__import');
        $this->setDescription('Import Teams');
        $this->addArgument   ('type', InputArgument::REQUIRED, 'karen');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'file');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $type = $input->getArgument('type');
        
        switch($type)
        {
            case 'karen': $this->importPersonFedsKaren($file); break;
        }        
        return; if ($output);
    }
    protected function importPersonFedsKaren($file)
    {   
        echo "import karen\n";
        return;
        $reader = $this->getService('cerad_game__project__teams__reader_eayso');
         
        $teams = $reader->read($project,$file);
        
        echo sprintf("Eayso Teams: %d\n",count($teams));
        
        file_put_contents($file . '.yml',Yaml::dump($teams,10));

        $saver = $this->getService('cerad_game__project__teams__saver_eayso');

        $results = $saver->save($teams,true);
        
        print_r($results);   
    }
}
?>
