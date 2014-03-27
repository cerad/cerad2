<?php
namespace Cerad\Bundle\AppCeradBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPersonsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_app__persons__import')
            ->setDescription('Import Persons');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
        
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $import = $this->getService('cerad_app_cerad.persons.import_yaml');
        
        $params = array('filepath' => 'data/Persons.yml', 'basename' => 'Persons.yml');
        
        $results = $import->process($params);
        
        echo $results . "\n";
        
        return;
    }
}
?>
