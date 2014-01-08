<?php
namespace Cerad\Bundle\EaysoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_eayso:certs:sync')
            ->setDescription('Sync Eayos Certs');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sync = $this->getService('cerad_eayso.certs.sync');
        
        $params = array('filepath' => 'data/report.referee.20131129.csv', 'basename' => 'referee20131129.csv');
        
        $results = $sync->process($params);
        
        echo sprintf("%s %s\n" . 
            "Total %d, MY %d, Fed %d Badge %d\n" .
            "Cert   Update %d Insert %d\n" .
            "Region Update %d Insert %d\n" .
            "Person Update %d\n" .
            "Duration %d\n",
            $results->message,
            $results->basename,
            $results->totalCertCount,
            $results->totalCertMYCount,
            $results->totalCertFedCount,
            $results->totalCertBadgeCount,
            $results->totalCertUpdateCount,
            $results->totalCertInsertCount,
            $results->totalRegionUpdateCount,
            $results->totalRegionInsertCount,
            $results->totalPersonUpdateCount,
            $results->duration
        );
        return;
    }
        
}
?>
