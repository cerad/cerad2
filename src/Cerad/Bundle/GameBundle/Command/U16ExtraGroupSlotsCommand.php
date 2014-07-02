<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

/* =========================================================
 * Changed U16 from two pools to one pool
 * Strange issue with individual game team group slots not updating
 * Showed up on team export
 */
class U16ExtraGroupSlotsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__u16_extra_group_slots');
        $this->setDescription('U16 Extra Group Slots');
      //$this->addArgument   ('file', InputArgument::REQUIRED, 'file');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
        
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gameTeamRepo = $this->getService('cerad_game__game_team_repository');
        
        $projectKey = 'AYSONationalGames2014';
        
        foreach(array('AYSO_U16B_Extra','AYSO_U16G_Extra') as $levelKey)
        {
            $gameTeams = $gameTeamRepo->findAllByProjectlevel($projectKey,$levelKey);
            foreach($gameTeams as $gameTeam)
            {
                $groupSlot = $gameTeam->getGroupSlot();
                if ($groupSlot[0] == 'B')
                {
                    echo sprintf("%s %s\n",$gameTeam->getGroupSlot(),$gameTeam->getTeamKey());
                    $gameTeam->setTeam(null);
                }
            }
        }
        $gameTeamRepo->flush();
        
        // Done
        return; if ($input && $output);
    }
 }
?>
