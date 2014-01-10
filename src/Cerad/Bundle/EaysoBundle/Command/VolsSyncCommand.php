<?php
namespace Cerad\Bundle\EaysoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VolsSyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_eayso:sync:vols');
        $this->setDescription('Sync Eayos Volunteers');
        $this->addArgument   ('filepath', InputArgument::REQUIRED, 'Vol CSV File');
      //$this->addArgument   ('truncate', InputArgument::OPTIONAL, 'Truncate');
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $params = array();
        $params['filepath'] = $input->getArgument('filepath');
        $params['basename'] = basename($params['filepath']);
        
        $sync = $this->getService('cerad_eayso.vols.sync');
        
        $results = $sync->process($params);
         
        $output->write($results->__toString());
    }
        
}
?>
