<?php
namespace Cerad\Bundle\PersonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CascadeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad:person:cascade')
            ->setDescription('Update person');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $personRepo = $this->getService('cerad_person.person_repository');
        $person = $personRepo->find(1);
        
        $personFed = $person->getFed('AYSOV',false);
        $certs = $personFed->getCerts();
        
        echo sprintf("Person %s %s\n",$person->getName()->full,$personFed->getId());
        
        $fedId = 'AYSOV99427977';
        
      //$personFed->setId($fedId);
      //echo sprintf("Certs %s %s %d\n",get_class($personFed),get_class($certs),count($certs));
        
        foreach($certs as $cert)
        {
            echo sprintf("Person Cert %s %s\n",$cert->getRole(),$cert->getBadge());
          //$cert->setFedId($fedId);
        }
        foreach($personFed->getOrgs() as $org)
        {
            echo sprintf("Person Cert %s %s\n",$org->getRole(),$org->getOrgId());
          //$org->setFedId($fedId);
        }
      //$personRepo->commit();
        
        return;
        
        $persons = $personRepo->findAll();
        foreach($persons as $person)
        {
            if (!$person->getGuid())
            {
                $person->setGuid($this->genGuid());
            }
        }
        $personRepo->commit();
    }
    protected function genGuid() 
    { 
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', 
            mt_rand(0,     65535), mt_rand(0,     65535), mt_rand(0, 65535), 
            mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), 
            mt_rand(0,     65535), mt_rand(0,     65535));  
    }
}
?>
