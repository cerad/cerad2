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
    
    protected $persons;
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processPersons();
        
        return;
        
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
        echo sprintf("Commited Fields: %4d\n",count($gameFieldRows));
        
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
            
            $this->processGameReport($game,$row['datax']);
            
            $gameRepo->save($game);
        }
        $gameRepo->commit();
        echo sprintf("Commited Games : %4d\n",count($gameRows));
        
        /* =====================================================================
         * Game Teams
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
            $gameTeam->setOrgId ($row['org_id']);
            
            $name  = $row['desc1'];
            $gameTeam->setName (substr($name,3));
            $gameTeam->setGroup(substr($name,0,2));
            
            // Process the report
            $this->processGameTeamReport($gameTeam,$row['gameTeamReport']);            
        }
        $gameRepo->commit();
        echo sprintf("Commited Teams : %4d\n",count($gameTeamRows));
        
        /* =====================================================================
         * Game Officials
         */
        $gameOfficialSql = <<<EOT
SELECT 
    person.*,
    personReg.reg_key  AS fedId,
    personReg.org_id   AS orgId,
    personReg.datax    AS regData,
    gameOfficial.type  AS gameOfficialRole, 
    gameOfficial.state AS gameOfficialState, 
    game.num           AS gameNum
FROM event_person      AS gameOfficial 
LEFT JOIN event        AS game      ON game.id   = gameOfficial.event_id 
LEFT JOIN person       AS person    ON person.id = gameOfficial.person_id
LEFT JOIN person_reg   AS personReg ON personReg.person_id = person.id
                
WHERE game.project_id = $projectIdx;
EOT;
        $gameOfficialRows = $conn->fetchAll($gameOfficialSql);
        
        foreach($gameOfficialRows as $row)
        {
            $num = $row['gameNum'];
            $game = $gameRepo->findOneByProjectNum($projectId,$num);
            
            $slot = null;
            $role = $row['gameOfficialRole'];
            switch($role)
            {
                case 'CR':   $slot = 1; break;
                case 'AR 1': $slot = 2; break;
                case 'AR 2': $slot = 3; break;
                default: die('Game Official Role: ' . $role);
            }
            $gameOfficial = $game->getOfficialForSlot($slot);
            if (!$gameOfficial)
            {
                $gameOfficial = $game->createGameOfficial();
                $gameOfficial->setSlot($slot);
                $gameOfficial->setRole($role);
                $game->addOfficial($gameOfficial);
            }
            $gameOfficial->setState($row['gameOfficialState']);
            
            $gameOfficial->setPersonNameFirst($row['first_name']);
            $gameOfficial->setPersonNameLast ($row['last_name']);
            $gameOfficial->setPersonEmail    ($row['email']);
            $gameOfficial->setPersonPhone    ($row['cell_phone']);
            
            $gameOfficial->setPersonFedId($row['fedId']);
            $gameOfficial->setPersonOrgId($row['orgId']);
            
            $data= unserialize($row['regData']);
            $gameOfficial->setPersonBadge($data['ref_badge']);
            
          //print_r($data); die();
        }
        $gameRepo->commit();
        echo sprintf("Commited Offs  : %4d\n",count($gameOfficialRows));
        
        return;
        
    }
    /* ===================================================
     * Break out the game team report processing
     */
    protected function processGameReport($game,$report)
    {
        // Dink around with unserializing
        $report = unserialize($report);
        
        if (!is_array($report)) return;
        
        if (!isset($report['report']))
        {
            // numx => 10GA04 - Think it has to do with printing?
            if (isset($report['numx'])) return;
            
            print_r($report); die();
        }
        $reportText   = $report['report'];
        $reportStatus = $report['reportStatus'];
        
        $gameReport = $game->createGameReport();
        $gameReport->setText  ($reportText);
        $gameReport->setStatus($reportStatus);
        
        $game->setReport($gameReport);
        
        return;
        print_r($report); die();
    }
    /* ===================================================
     * Break out the game team report processing
     */
    protected function processGameTeamReport($gameTeam,$report)
    {
        // Dink around with unserializing
        $report = unserialize($report);
        
        if (!is_array($report)) return;
        
        foreach($report as $key => $value)
        {
            switch($key)
            {
                case 'report': break;
                default:
                    echo sprintf("*** Unkown report key %s\n",$key);
                    die();
            }
        }
        // Triggers error if no report
        $reportData = $report['report'];
        
        $transform = array(
            'cautions'    => 'playerWarnings',
            'sendoffs'    => 'playerEjections',
            'coachTossed' => 'coachEjections',
            'specTossed'  => 'specEjections',
        );
        foreach($transform as $key => $value)
        {
            if (array_key_exists($key,$reportData))
            {
                $reportData[$value] = $reportData[$key];
                unset($reportData[$key]);
            }
        }
        // Transfer data
        $gameTeamReport = $gameTeam->createGameTeamReport($reportData);
        $gameTeam->setReport($gameTeamReport);
        
        // Want to check for unknown prop names
        $propNames = $gameTeamReport->getPropNames();
        $extra = array();    
        foreach($reportData as $propName => $value)
        {
            if (!in_array($propName,$propNames))
            {
                $extra[$propName] = $value;
            }
        }
        if (count($extra))
        {
            print_r($extra);
            die('Extra Report Data');
        }        
    }
    /* ===============================================================
     * Build up an array of people
     */
    protected function processPersons()
    {
        // Map of personId to person/personGuid
        $this->persons = array();
        
        $projectId  = self::PROJECT_ID;
        $projectIdx = self::PROJECT_IDX;
        
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $personRepo = $this->getService('cerad_person.person_repository');
        $personRepo->truncate();
        
        $personSql = <<<EOT
SELECT person.*
FROM   person;
EOT;
        $personRows = $conn->fetchAll($personSql);

        foreach($personRows as $row)
        {
            $this->processPersonDatax($row,$row['datax']);
            
            $personId = $row['id'];
            
            $person = $personRepo->createPerson();

            /* ======================================================
             * The name stuff using wonderful value object
             */
            $name = $person->getName();
            $name->first = $row['first_name'];
            $name->last  = $row['last_name'];
            $name->nick  = $row['nick_name'];
            
            $nameLast = $name->last ? ' ' . $name->last : null;
            
            $name->full = $nameLast;
            
            if ($name->first) $name->full = $name->first . $nameLast;
            if ($name->nick ) $name->full = $name->nick  . $nameLast;
            
            $person->setName($name);
            
            /* =======================================================
             * Misc stuff
             */
            $person->setStatus  ($row['status'  ]);
            $person->setVerified($row['verified']);
            
            $person->setEmail ($row['email']);
            $person->setPhone ($row['cell_phone']);
            $person->setGender($row['gender']);
            
            $dob1 = $row['dob'];
            $dob2 = sprintf('%s-%s-%s',
                substr($dob1,0,4),substr($dob1,4,2),substr($dob1,6,2));
                
            $dob3 = \DateTime::createFromFormat('Y-m-d',$dob2);
            $person->setDob($dob3);
            
            // Persist
            $personRepo->save($person);
            
            // Stash
            $this->persons[$personId] = $person->getGuid();
        }
        $personRepo->commit();
        
        echo sprintf("Commited Pers  : %4d\n",count($personRows));
   }
    /* ====================================================================
     * There is a datax but looks to be empty
     */
    protected function processPersonDatax($row,$datax)
    {
        if (!$datax) return;
        
        $data = unserialize($row['datax']);
        if (is_array($data))
        {
            if (count($data))
            {
                print_r($row);
                print_r($data);
                die();
            }
            return;
        }
        // One record with bool false
        if (is_bool($data)) return;
        
        echo sprintf("datax: %s\n",$datax);
    }        
}
?>
