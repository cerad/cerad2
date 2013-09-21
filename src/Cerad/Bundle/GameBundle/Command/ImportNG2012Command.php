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
        $projectId = self::PROJECT_ID;
        
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        /* =======================================================================
         * Fields
         */
        $gameFieldRepo = $this->getService('cerad_game.game_field_repository');
        
        $rows = $conn->fetchAll('SELECT * FROM project_field where project_id = 52;');

        foreach($rows as $row)
        {
            $name  = $row['key1'];
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
        return;
        
        $importFile = $input->getArgument('importFile');
        $truncate   = $input->getArgument('truncate');
        
        if ($truncate) $truncate = true;
        
        $this->loadFile($importFile,$truncate);

    }
    /* =========================================================
     * gws/TEST_Fall2013_GamesWithSlots_20130917.xml
     * 
     * pathInfo
     *  [dirname]   => gws
     *  [basename]  => TEST_Fall2013_GamesWithSlots_20130917.xml
     *  [extension] => xml
     *  [filename]  => TEST_Fall2013_GamesWithSlots_20130917
     * 
     * parts
     * [0] => domain => TEST
     * [1] => season => Fall2013
     * [2] => format => GamesWithSlots
     * [3] => suffix => 20130917
     */
    protected function loadFile($filePath, $truncate)
    {   
        // Add in default directory and make sure it exists
        $datax  = $this->getParameter('cerad_datax');
        
        $filePath = $datax . $filePath;
        
        if (!file_exists($filePath)) 
        {
            echo sprintf("*** File does not exist: %s\n",$filePath);
            return;
        }
        
        $pathInfo = pathinfo($filePath);
        
        $parts = explode('_',$pathInfo['filename']);
        
        if (count($parts) < 4)
        {
            echo sprintf("*** %d File Name Format: DOMAIN_Season_Format_Date\n",count($parts));
            return;
        }
        
        $paramsx = array();
        
        $paramsx['filepath'] = $filePath;
        
        $params = array_merge($paramsx,$pathInfo);
        
        $params['sport']  = 'Soccer';
        $params['domain'] = $parts[0];
        $params['season'] = $parts[1];
        $params['format'] = $parts[2];
        $params['suffix'] = $parts[3];
        
        $importServiceId = sprintf('cerad_game.schedule_Arbiter%s.import',$params['format']);
        $importService = $this->getService($importServiceId);
        
        $results = $importService->import($params);
        
        print_r($results);
        
    }
}
?>
