<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssignRolesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__assign_roles');
        $this->setDescription('Fix up assign roles');
        $this->addArgument   ('token', InputArgument::REQUIRED, 'Token');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Little security
        $token = $input->getArgument('token');
        if ($token != 894) return;
        
        $gameRepo = $this->getService('cerad_game__game_repository');
        
        $projectKey = 'AYSONationalGames2014';
        
        $groupTypes = array('QF','SF','FM');
        
        $criteria = array(
            'groupTypes'    => $groupTypes,
            'projectKeys'   => array($projectKey),
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        echo sprintf("Fix Roles Game Count %d\n",count($games));
        
        foreach($games as $game)
        {
            $gameOfficials = $game->getOfficials();
            foreach($gameOfficials as $gameOfficial)
            {
                $gameOfficial->setAssignRole('ROLE_ASSIGNOR');
            }
        }
        $gameRepo->flush();
                
        return; if ($output);
    }
}
?>
