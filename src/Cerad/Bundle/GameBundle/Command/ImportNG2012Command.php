<?php
namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportNG2012Command extends ContainerAwareCommand
{
    protected $commandName = 'command';
    protected $commandDesc = 'Command Description';
    
    protected function configure()
    {
        $this
            ->setName       ('cerad:import:ng2012')
            ->setDescription('Schedule Import');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    const PROJECT_ID  = 'AYSONationalGames2012';
    const PROJECT_IDX = 52;
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectId  = self::PROJECT_ID;
        $projectIdx = self::PROJECT_IDX;
        
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $gameRepo      = $this->getService('cerad_game.game_repository');
        $gameFieldRepo = $this->getService('cerad_game.game_field_repository');
        
        /* =======================================================================
         * Fields
         */
        $gameFieldSql = <<<EOT
SELECT gameField.key1 AS name, gameField.venue AS venue
FROM   project_field AS gameField
WHERE  gameField.project_id = $projectIdx;
EOT;
        $gameFieldRows = $conn->fetchAll($gameFieldSql);

        foreach($gameFieldRows as $row)
        {
            $name  = $row['name'];
            $venue = $row['venue'];
            
            $gameField = $gameFieldRepo->findOneByProjectName($projectId,$name);
            if (!$gameField)
            {
                $gameField = $gameFieldRepo->createGameField();
                $gameField->setName     ($name);
                $gameField->setVenue    ($venue);
                $gameField->setProjectId($projectId);
                $gameFieldRepo->save($gameField);
            }
        }
        $gameFieldRepo->commit();
 
        /* =====================================================================
         * Games
         */
        $gameSql = <<<EOT
SELECT event.* ,field.key1 AS field_name FROM event 
LEFT JOIN project_field AS field ON event.field_id = field.id 
WHERE event.project_id = $projectIdx;
EOT;
        $gameRows = $conn->fetchAll($gameSql);

        foreach($gameRows as $row)
        {
            $num = $row['num'];
            $game = $gameRepo->findOneByProjectNum($projectId,$num);
            if (!$game)
            {
                $game = $gameRepo->createGame();
                $game->setNum($num);
                $game->setProjectId($projectId);
            }
            $pool = $row['pool'];
            $levelId = 'AYSO_' . substr($pool,0,4) . '_Core';
            $game->setLevelId($levelId);
            $game->setGroup(substr($pool,5));
                
            $gameField = $gameFieldRepo->findOneByProjectName($projectId,$row['field_name']);
            $game->setField($gameField);
                
                $datex = $row['datex'];
                $timex = $row['timex'];
                $dt = sprintf('%s-%s-%s %s:%s:00',
                    substr($datex,0,4),substr($datex,4,2),substr($datex,6,2),
                    substr($timex,0,2),substr($timex,2,2));
                
                $dtBeg = \DateTime::createFromFormat('Y-m-d H:i:s',$dt);
                $dtEnd = clone($dtBeg);
                $dtEnd->add(new \DateInterval('PT55M'));
                
                $game->setDtBeg($dtBeg);
                $game->setDtEnd($dtEnd);
                
                $gameRepo->save($game);
        }
        $gameRepo->commit();
        
        /* =====================================================================
         * Games
         */
        $gameTeamSql = <<<EOT
SELECT 
    team.*,
    gameTeam.type  AS gameTeamRole, 
    gameTeam.datax AS gameTeamReport,
    game.num       AS gameNum
FROM event_team AS gameTeam 
LEFT JOIN event AS game ON game.id = gameTeam.event_id 
LEFT JOIN team  AS team ON team.id = gameTeam.team_id
WHERE game.project_id = $projectIdx;
EOT;
        $gameTeamRows = $conn->fetchAll($gameTeamSql);
        
        foreach($gameTeamRows as $row)
        {
            $num = $row['gameNum'];
            $game = $gameRepo->findOneByProjectNum($projectId,$num);
 
            switch($row['gameTeamRole'])
            {
                case 'Home': $gameTeam = $game->getHomeTeam(); break;
                case 'Away': $gameTeam = $game->getAwayTeam(); break;
                default: die('bad gameTeam role ' . $role);
            }
            $gameTeam->setLevelId($game->getLevelId());
            $gameTeam->setReport($row['gameTeamReport']);
            $gameTeam->setOrgId ($row['org_id']);
            
            $name  = $row['desc1'];
            $gameTeam->setName (substr($name,3));
            $gameTeam->setGroup(substr($name,0,2));
            
            //print_r($row); die();
        }
        $gameRepo->commit();
        
        return;
        
    }
}
?>
