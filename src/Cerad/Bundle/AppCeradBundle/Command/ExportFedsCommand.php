<?php
namespace Cerad\Bundle\AppCeradBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportFedsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_app:export:feds')
            ->setDescription('Export Feds');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
        
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $export = $this->getService('cerad_app_cerad.feds.export01_yaml');
        $writer = $export->process();
        
      //echo $writer->flush(false);
        
        file_put_contents('data/feds.yml',$writer->flush());
        
        echo sprintf("Exported Feds: %d\n",$export->getFedCount());
        return;
   }
}
?>
