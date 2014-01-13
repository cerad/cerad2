<?php
namespace Cerad\Bundle\AppCeradBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportFedsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_app:import:feds')
            ->setDescription('Import Feds');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
        
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      //echo sprintf("kernel.root_dir: %s\n", $this->getParameter('kernel.root_dir'));
        
        $import = $this->getService('cerad_app_cerad.feds.import01_yaml');
        
        $params = array('filepath' => 'data/feds.yml', 'basename' => 'Feds.yml');
        
        $results = $import->process($params);
        
        echo $results;
        
        return;
    }
}
?>
