<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class GamesUnloadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_game__games__unload');
        $this->setDescription('Unload Games');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'file');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $conn = $this->getService('doctrine.dbal.games_connection');
        
        $games = $this->unloadGames($conn);
        echo sprintf("Game count: %d\n",count($games));
        
        $teams = $this->unloadTeams($conn);
        echo sprintf("Team count: %d\n",count($teams));
        
        file_put_contents($file,Yaml::Dump(array(
            'teams' => $teams,
            'games' => $games),20));
        
        return; if ($output);
    }
    protected function unloadGames($conn)
    {   
        $gamesx = $conn->fetchAll('SELECT * FROM games ORDER BY projectKey,num');
        $games = array();
        foreach($gamesx as $game)
        {
            $game['report'] = unserialize($game['report']);
            
            $gameId = $game['id'];
            unset($game['id']);
            
            $game['teams']     = array();
            $game['officials'] = array();
            
            // Teams
            $teamsSql = 'SELECT * FROM game_teams WHERE gameId = :gameId ORDER BY slot;';
            
            $teamsStmt = $conn->prepare($teamsSql);
            $teamsStmt->execute(array('gameId' => $gameId));
            $teamsx = $teamsStmt->fetchAll();
            foreach($teamsx as $team)
            {
                $team['report'] = unserialize($team['report']);
                
                unset($team['id']);
                unset($team['gameId']);
                
                $game['teams'][] = $team;
            }
            // Officials
            $officialsSql = 'SELECT * FROM game_officials WHERE gameId = :gameId ORDER BY slot;';
            
            $officialsStmt = $conn->prepare($officialsSql);
            $officialsStmt->execute(array('gameId' => $gameId));
            $officialsx = $officialsStmt->fetchAll();
            foreach($officialsx as $official)
            {   
                unset($official['id']);
                unset($official['gameId']);
                
                $game['officials'][] = $official;
            }
            // Done
            $games[] = $game;
        }
        return $games;
    }
    protected function unloadTeams($conn)
    {   
        $teamsx = $conn->fetchAll('SELECT * FROM teams ORDER BY projectKey,levelKey,num');
        $teams = array();
        foreach($teamsx as $team)
        {
            unset($team['id']);
            
            $teams[] = $team;
        }
        return $teams;
    }
}
?>
