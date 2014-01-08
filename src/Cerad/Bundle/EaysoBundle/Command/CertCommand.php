<?php
namespace Cerad\Bundle\EaysoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/* ==================================================
 * Test for the cert service
 */
class CertCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_eayso:certs:test')
            ->setDescription('Test Cert Service');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
        
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo sprintf("Test Certs\n");
        $certRepo = $this->getService('cerad_eayso.cert_repository');
        
        $certs = $certRepo->findByCertDesc('Regional Referee & Safe Haven Referee');
        print_r($certs);
        return;
    }
 }
?>
