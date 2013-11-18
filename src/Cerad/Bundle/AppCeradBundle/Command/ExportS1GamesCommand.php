<?php
namespace Cerad\Bundle\AppCeradBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportS1GamesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_app:export:s1games')
            ->setDescription('Export S1Games');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
        
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $export = $this->getService('cerad_app_cerad.persons_s1games.export_xml');
        $writer = $export->process();
        
        echo $writer->flush(false);
        
        file_put_contents('Persons.xml',$writer->flush());
        

        
        return;
   }
}
?>
