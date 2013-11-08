<?php
namespace Cerad\Bundle\OrgBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ContainerAwareCommand
{
    protected $commandName = 'command';
    protected $commandDesc = 'Command Description';
    
    protected function configure()
    {
        $this
            ->setName       ('cerad:org:import')
            ->setDescription('Schedule Import')
            ->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File');
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Just hard code for now
        $filepath = $input->getArgument('importFile');
        
        $params['filepath'] = $filepath;
        $params['basename'] = basename($filepath);

        $import = $this->getService('cerad_org.orgs.import_xls');
        
        $results = $import->import($params);
        
        echo sprintf("Imported: %s %d %d\n",
                $results->basename,
                $results->totalOrgCount, 
                $results->modifiedOrgCount);
        
        return;    
    }
}
?>
