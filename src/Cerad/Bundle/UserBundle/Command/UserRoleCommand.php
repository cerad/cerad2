<?php
namespace Cerad\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class UserRoleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad:user:role')
            ->setDescription('User roles')
            ->addArgument   ('username', InputArgument::REQUIRED, 'User Name')
            ->addArgument   ('role',     InputArgument::OPTIONAL, 'Role')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $role     = $input->getArgument('role');
        
        $userProvider = $this->getService('cerad_user.user_provider');
        $userManager  = $this->getService('cerad_user.user_manager');
        
        try
        {
            $user = $userProvider->loadUserByUsername($username);
        }
        catch (\Exception $e)
        {
            echo sprintf("*** User not found: %s\n",$e->getMessage());
            return;
        }
        if ($role)
        {
            if ($user->hasRole($role))
            {
                $user->removeRole($role);
                $userManager->updateUser($user);
            }
            else
            {
                $user->addRole($role);
                $userManager->updateUser($user);
            }
        }
        $roles = $user->getRoles();
        $rolesx = implode(', ',$roles);
        echo sprintf("User: %s %s %s\n",$user->getUsername(),$user->getAccountName(),$rolesx);
    }
}
?>
