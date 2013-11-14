<?php
namespace Cerad\Bundle\AppCeradBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportS1GamesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_app:export:s1games')
            ->setDescription('Export S1Games');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
        
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $export = $this->getService('cerad_app_cerad.persons_s1games.export_xml');
        $writer = $export->process();
        
        echo $writer->outputMemory(true);
        
        return;
    }
    /* =====================================================================
     * Games
     */
    protected function processGames()
    {
      $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $gameRepo      = $this->getService('cerad_game.game_repository');
        $gameFieldRepo = $this->getService('cerad_game.game_field_repository');

        $gameSql = <<<EOT
SELECT event.* ,field.key1 AS field_name FROM event 
LEFT JOIN project_field AS field ON event.field_id = field.id 
WHERE event.project_id IN (52,62);
EOT;
        $gameRows = $conn->fetchAll($gameSql);

        foreach($gameRows as $row)
        {
            $num = $row['num'];
            
            $projectId = $this->getProjectId($row['project_id']);
            
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
            
            $this->processGameReport($game,$row['datax']);
            
            $gameRepo->save($game);
        }
        $gameRepo->commit();
        $gameRepo->clear();
        $gameFieldRepo->clear();
        echo sprintf("Commited Games : %4d\n",count($gameRows));        
    }
}
?>
