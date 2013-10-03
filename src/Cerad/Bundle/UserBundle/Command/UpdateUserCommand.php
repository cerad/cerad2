<?php
namespace Cerad\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad:user:update')
            ->setDescription('Update user');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userRepo   = $this->getService('cerad_user.user_repository');
        $personRepo = $this->getService('cerad_person.person_repository');
        
        $users = $userRepo->findAll();
        foreach($users as $user)
        {
            $guid = $user->getPersonGuid();
            $person = $personRepo->findOneByFedId($guid);
            if ($person)
            {
                $user->setPersonGuid($person->getGuid());
            }
        }
        $userRepo->commit();
    }
}
?>
