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
        
    protected $persons;
    protected $accounts;
    protected $accountPersons; // Only one account per person
    
    protected function getProjectId($id)
    {
        switch($id)
        {
            case 52: return 'AYSONationalGames2012'; 
            case 62: return 'AYSOS5Games2012';
        }
        echo sprintf("*** Invalid project id: %d\n",$id);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processPersons();
      //$this->processPersonPlans();
      //$this->processPersonPersons();
        
      //$this->processAccounts();
      //
        $this->processGameFields();
        $this->processGames();
        $this->processGameTeams();
        $this->processGameOfficials();
        
        return;
    }
    /* =====================================================================
     * Game Officials
     */
    protected function processGameOfficials()
    {
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $gameRepo   = $this->getService('cerad_game.game_repository');
        $personRepo = $this->getService('cerad_person.person_repository');
        
        $gameOfficialSql = <<<EOT
SELECT 
    person.*,
    personReg.reg_key  AS fedId,
    personReg.org_id   AS orgId,
    personReg.datax    AS regData,
    gameOfficial.type  AS gameOfficialRole, 
    gameOfficial.state AS gameOfficialState, 
    game.num           AS gameNum,
    game.project_id    AS projectId
                
FROM event_person      AS gameOfficial 
LEFT JOIN event        AS game      ON game.id   = gameOfficial.event_id 
LEFT JOIN person       AS person    ON person.id = gameOfficial.person_id
LEFT JOIN person_reg   AS personReg ON personReg.person_id = person.id
                
WHERE game.project_id IN (52,62);
EOT;
        $gameOfficialRows = $conn->fetchAll($gameOfficialSql);
        
        foreach($gameOfficialRows as $row)
        {
            // Should have person for everyone
            $personId = $row['id'];
            if ($personId)
            {
                $personGuid = $this->persons[$personId];
                $person = $personRepo->findOneByGuid($personGuid);
                $personFed = $person->getFed('AYSOV');
                
                /* =================================================
                 * Originally, personFed was not being loaded
                 * Clearing the cache fixed it
                 */
                if ($personFed->getId() != $row['fedId'])
                {
                    echo sprintf("*** Person id: %d, guid: %s\n",$person->getId(),$personGuid);
                    echo sprintf("*** AYSOV Mismatch PF: %s, GO: %s\n",$personFed->getId(),$row['fedId']);
                    die();
                }
            }
            // Back to the game
            $num = $row['gameNum'];
            $projectId = $this->getProjectId($row['projectId']);
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
            
            if ($personId)
            {
                $gameOfficial->setPersonGuid($this->persons[$personId]);
            }
            $data= unserialize($row['regData']);
            $gameOfficial->setPersonBadge($data['ref_badge']);
            
          //print_r($data); die();
        }
        $gameRepo->commit();
        $gameRepo->clear();
        echo sprintf("Commited Offs  : %4d\n",count($gameOfficialRows));
        
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
    /* =====================================================================
     * Game Teams
     */
    protected function processGameTeams()
    {
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $gameRepo = $this->getService('cerad_game.game_repository');
               
        
        /* =====================================================================
         * Game Teams
         */
        $gameTeamSql = <<<EOT
SELECT 
    team.*,
    gameTeam.type   AS gameTeamRole, 
    gameTeam.datax  AS gameTeamReport,
    game.num        AS gameNum,
    game.project_id AS projectId
FROM event_team AS gameTeam 
LEFT JOIN event AS game ON game.id = gameTeam.event_id 
LEFT JOIN team  AS team ON team.id = gameTeam.team_id
WHERE game.project_id IN (52,62);
EOT;
        $gameTeamRows = $conn->fetchAll($gameTeamSql);
        
        foreach($gameTeamRows as $row)
        {
            $num = $row['gameNum'];
            $projectId = $this->getProjectId($row['projectId']);
            $game = $gameRepo->findOneByProjectNum($projectId,$num);
 
            switch($row['gameTeamRole'])
            {
                case 'Home': $gameTeam = $game->getHomeTeam(); break;
                case 'Away': $gameTeam = $game->getAwayTeam(); break;
                default: die('bad gameTeam role ' . $row['gameTeamRole']);
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
        $gameRepo->clear();
        echo sprintf("Commited Teams : %4d\n",count($gameTeamRows));
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
    /* =======================================================================
     * Fields
     */
    protected function processGameFields()
    {
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $gameFieldRepo = $this->getService('cerad_game.game_field_repository');
 
        $gameFieldSql = <<<EOT
SELECT 
    gameField.project_id AS projectId, 
    gameField.key1       AS name, 
    gameField.venue      AS venue
FROM   project_field AS gameField
WHERE  gameField.project_id IN (52,62);
EOT;
        $gameFieldRows = $conn->fetchAll($gameFieldSql);

        foreach($gameFieldRows as $row)
        {
            $name  = $row['name'];
            $venue = $row['venue'];
            
            $projectId = $this->getProjectId($row['projectId']);
            
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
        $gameFieldRepo->clear();
        echo sprintf("Commited Fields: %4d\n",count($gameFieldRows));
    }
    /* ===============================================================
     * Build up an array of people
     */
    protected function processPersons()
    {
        // Map of personId to person/personGuid
        $this->persons = array();
        
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $personRepo = $this->getService('cerad_person.person_repository');
      //$personRepo->truncate();
        
        $personSql = <<<EOT
SELECT 
    person.*, 
    personFed.reg_key AS fedId,
    personFed.org_id  AS orgId,
    personFed.datax   AS fedData
FROM person
LEFT JOIN person_reg AS personFed ON personFed.person_id = person.id
;
EOT;
        $personRows = $conn->fetchAll($personSql);

        foreach($personRows as $row)
        {
            // This does nothing but verify that there is nothing to do
            $this->processPersonDatax($row,$row['datax']);
            
            // The fed is the immmutable aysoid
            $personFed = $personRepo->findFed($row['fedId']);
            if ($personFed) $person = $personFed->getPerson();
            else
            {
                $person = $personRepo->createPerson();
                $personFed = $person->getFed('AYSOV');
                $personFed->setId($row['fedId']);
            }

            $this->processPersonFedDatax($personFed,$row['orgId'],$row['fedData']);
            
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
            $personId = $row['id'];
            $this->persons[$personId] = $person->getGuid();
        }
        $personRepo->commit();
        
        echo sprintf("Commited Pers  : %4d\n",count($personRows));
   }
    /* ====================================================================
     * All have an array datax
     * ref_badge - All have, many with a value of none
     * ref_date
     * safe_haven
     * mem_year  - Missing if never looked up
     */
    protected function processPersonFedDatax($personFed,$orgId,$datax)
    {
        if (!$datax) return;
        $data = unserialize($datax);
        if (!is_array($data))
        {
            echo sprintf("personFed.datax is not array: %s\n",$datax);
            die();
        }
        // Unpack
        $refBadge  = isset($data['ref_badge'])  ? $data['ref_badge']  : null;
        $refDate   = isset($data['ref_date' ])  ? $data['ref_date' ]  : null;
        $safeHaven = isset($data['safe_haven']) ? $data['safe_haven'] : null;
        $memYear   = isset($data['mem_year'])   ? $data['mem_year']   : null;
        
        // All seem to have an org_id
        if ($orgId)
        {
            $personFedOrg = $personFed->getOrgRegion();
            $personFedOrg->setOrgId($orgId);
            
            if ($memYear == 'None') $memYear = null;
            
            $personFedOrg->setMemYear($memYear);
        }
        // Process safe haven is have one
        if ($safeHaven)
        {
            $certSafeHaven = $personFed->getCertSafeHaven();
            $certSafeHaven->setBadge($safeHaven);
        }
        if ($refBadge == 'None') $refBadge = null;
        if ($refBadge)
        {
            $certReferee = $personFed->getCertReferee();
            $certReferee->setBadge ($refBadge);
          //$certReferee->setBadgex($refBadge);
            
            if ($refDate)
            {
                $refDate2 = sprintf('%s-%s-%s',
                    substr($refDate,0,4),substr($refDate,4,2),substr($refDate,6,2));
                
                $refDate3 = \DateTime::createFromFormat('Y-m-d',$refDate2);
                
                $certReferee->setDateFirstCertified($refDate3);
            }
        }
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
    /* =====================================================================
     * Called after the persons have been processed
     * Add in all the project plans
     */
    protected function processPersonPlans()
    {
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $personRepo = $this->getService('cerad_person.person_repository');
        
        $personPlanSql = <<<EOT
SELECT plan.*
FROM   project_person AS plan
WHERE  plan.project_id IN (52,62)
;
EOT;
        $personPlanRows = $conn->fetchAll($personPlanSql);
        foreach($personPlanRows as $row)
        {
            $personGuid = $this->persons[$row['person_id']];
            $person = $personRepo->findOneByGuid($personGuid);
            
            switch($row['project_id'])
            {
                case 52: 
                    $projectId = 'AYSONationalGames2012'; 
                    $plan = $person->getPlan($projectId);
                    $plan->setBasic($this->getBasicPlanInfo());
                    $this->processNatGamesPlan($plan,unserialize($row['datax']));
                    break;
                
                case 62: 
                    $projectId = 'AYSOS5Games2012';       
                    $plan = $person->getPlan($projectId);
                    $plan->setBasic($this->getBasicPlanInfo());
                    $this->processS5GamesPlan($plan,unserialize($row['datax']));
                    break;
            }
        }
        $personRepo->commit();
    }
    protected function processNatGamesPlan($plan,$data)
    {
        if (!is_array($data)) return;
        if (!isset($data['plans'])) return;
        
        $info = $data['plans'];
                
        $basic = $this->getBasicPlanInfo();
        
        $map = array(
            'attending'    => 'attend',
            'refereeing'   => 'will_referee',
            'willAssess'   => 'do_assessments',
            'wantAssess'   => 'want_assessment',
            'coaching'     => 'coaching',
            'playing'      => 'have_player',
            'volunteering' => 'other_jobs',
            'tshirt'       => 't_shirt_size',
            'opening'      => 'attend_open',
            'shuttle'      => 'ground_transport',
            'housing'      => 'hotel',

        );
        foreach($map as $key1 => $key2)
        {
            if (isset($info[$key2])) $basic[$key1] = $info[$key2];
        }
        $plan->setBasic($basic);
    }
    protected function processS5GamesPlan($plan,$data)
    {
        if (!is_array($data)) return;
        if (!isset($data['plans'])) return;
        
        $info = $data['plans'];
        
        $basic = $plan->getBasic();
        $map = array(
            'attending'    => 'willAttend',
            'refereeing'   => 'willReferee',
            'willAssess'   => 'willAssess',
            'wantAssess'   => 'requestAssess',
            'coaching'     => 'willCoach',
            'playing'      => 'havePlayer',
            'volunteering' => 'willVolunteer',
            'tshirt'       => 'tshirtSize',
            'notes'        => 'notes',
        );
        foreach($map as $key1 => $key2)
        {
            $basic[$key1] = $info[$key2];
        }
        $plan->setBasic($basic);
    }
    protected function getBasicPlanInfo()
    {
        return array
        (
                'attending'    => null,
                'refereeing'   => null,
                'willInstruct' => null,
                'willAssess'   => null,
                'wantAssess'   => null,
                'coaching'     => null,
                'volunteering' => null,
                'playing'      => null,
                'opening'      => null,
                'tshirt'       => null,
                'shuttle'      => null,
                'housing'      => null,
                'notes'        => null,
        );
    }
    /* =====================================================================
     * Called after the persons have been processed
     * Add in all the person person relations
     */
    protected function processPersonPersons()
    {
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $personRepo = $this->getService('cerad_person.person_repository');
        
        $personPersonSql = <<<EOT
SELECT person_person.*
FROM   person_person
;
EOT;
        $personPersonRows = $conn->fetchAll($personPersonSql);
        foreach($personPersonRows as $row)
        {
            $personGuid1 = $this->persons[$row['person_id1']];
            $person1 = $personRepo->findOneByGuid($personGuid1);
            
            $personGuid2 = $this->persons[$row['person_id2']];
            $person2 = $personRepo->findOneByGuid($personGuid2);
            
            $role = $row['relation'];
            
            $personPerson = $person1->createPersonPerson();
            $personPerson->setRole($role);
            $personPerson->setParent($person1);
            $personPerson->setChild ($person2);
            
            $person1->addPersonPerson($personPerson);
        }
        $personRepo->commit();
    }
    /* =====================================================================
     * Called after the persons have been processed
     * Add in all the person person relations
     * 
     * Remember to clear the user table for a full reset
     */
    protected function processAccounts()
    {
        $conn = $this->getService('doctrine.dbal.ng2012_connection');
        
        $userManager = $this->getService('cerad_user.user_manager');
        $personRepo  = $this->getService('cerad_person.person_repository');
        
        $accountSql = <<<EOT
SELECT account.*
FROM   account
;
EOT;
        $accountRows = $conn->fetchAll($accountSql);
        foreach($accountRows as $row)
        {
            $this->processAccount($userManager,$personRepo,$row);    
        }
    }
    protected function processAccount($userManager,$personRepo,$row)
    {
        // Only allow one account per person mostly because emails need to be unique
        $personId  = $row['person_id'];
        $accountId = $row['id'];
        if (isset($this->accountPersons[$personId])) return;
        
        // Grab the person stuff
        $personGuid = $this->persons[$personId];
        $person = $personRepo->findOneByGuid($personGuid);
        $personName = $person->getName();
            
        $username = $row['user_name'];
        $userpass = $row['user_pass'];
            
        if (strlen($userpass) != 32)
        {
            // Some have blank for password
            $userpass = md5('zaysox');
        }
        // For now, no need to update
        $userExisting = $userManager->findUserByUsername($username);
        if ($userExisting) return;
        
        // See if email was already used
        $email = $person->getEmail();
        $userEmail = $userManager->findUserByEmail($email);
        if ($userEmail)
        {
            // Just a few
          //echo sprintf("Dup email: %d %d %s\n",$accountId,$personId,$email);
            return;
        }
        // Shoud be good to go
        $user = $userManager->createUser();
        $user->setUsername   ($username);
        $user->setPassword   ($userpass);
        $user->setEmail      ($email);
        $user->setAccountName($personName->full);
        $user->setPersonGuid ($person->getGuid());
            
        // Persist
        $userManager->updateUser($user);
        $this->accounts     [$accountId] = $username;
        $this->accountPersons[$personId] = $username;
        
    }
}
?>
