<?php

namespace Cerad\Bundle\PersonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class PersonFedImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_person__person_fed__import');
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
            case 'karen': $this->importPersonFedKaren($file); break;
        }        
        return; if ($output);
    }
    protected function importPersonFedKaren($file)
    {   
        echo "import karen\n";
        
        $reader = $this->getService('cerad_person__person_fed__reader_karen');
         
        $items = $reader->read($file);
        
        echo sprintf("Person Feds: %d\n",count($items));
        
        file_put_contents($file . '.yml',Yaml::dump($items,10));

        $saver = $this->getService('cerad_person__person_fed__saver_karen');

        $results = $saver->save($items,true);
        
        echo sprintf("Total %d, MemYear %d, SafeHaven %d\n",
            $results->total,
            $results->updatedMemYear,
            $results->updatedSafeHaven);
        
        foreach($results->missing as $missing)
        {
            echo sprintf("Missing %s %s %s\n",$missing['fedKey'],$missing['name'],$missing['email']);
        } 
    }
}
?>
