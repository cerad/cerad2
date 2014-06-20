<?php

namespace Cerad\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Role\Role;

class RolesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_core__roles');
        $this->setDescription('Role Hierarchy');
        $this->addArgument   ('roleName', InputArgument::REQUIRED, 'role');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roleHeir = $this->getService('cerad_core__role_hierarchy');
        
        $roleName = $input->getArgument('roleName');
        $roleIn = new Role($roleName);
        $roles = $roleHeir->getReachableRoles(array($roleIn));
        echo "\n";
        foreach($roles as $role)
        {
            echo sprintf("%s reaches  %s\n",$roleName,$role->getRole());
        }
        echo "\n";
        
        $roleHeirRoles = $this->getParameter('security.role_hierarchy.roles');
        $roleItems = isset($roleHeirRoles[$roleName]) ? $roleHeirRoles[$roleName] : array();
        foreach($roleItems as $roleItem)
        {
            echo sprintf("%s ancestor %s\n",$roleName,$roleItem);
        }
     }
}
?>
