<?php

namespace Cerad\Bundle\PersonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class PersonsUnloadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_person__persons__unload');
        $this->setDescription('Unload Persons');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'file');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $conn = $this->getService('doctrine.dbal.default_connection');
        
        $users = $this->unloadUsers($conn);
        echo sprintf("User count: %d\n",count($users));
        
        $persons = $this->unloadPersons($conn);
        echo sprintf("Person count: %d\n",count($persons));
        
        file_put_contents($file,Yaml::Dump(array(
            'persons' => $persons,
            'users'   => $users),20));
        
        return; if ($output);
    }
    protected function unloadPersons($conn)
    {   
        $personsx = $conn->fetchAll('SELECT * FROM persons ORDER BY id');
        
        $personKeys = array();
        foreach($personsx as $person)
        {
            $personKeys[$person['id']] = $person['guid'];
        }
        $persons = array();
        foreach($personsx as $person)
        {
          //$person['notes'] = unserialize($person['notes']);
            
            $personId = $person['id'];
            
            // Need for person person
            //unset($person['id']);
            
            $person['feds']    = array();
            $person['plans']   = array();
            $person['teams']   = array();
            $person['persons'] = array();
            
            // Teams
            $teamsSql = 'SELECT * FROM person_teams WHERE person_id = :personId;';
            
            $teamsStmt = $conn->prepare($teamsSql);
            $teamsStmt->execute(array('personId' => $personId));
            $teamsx = $teamsStmt->fetchAll();
            foreach($teamsx as $team)
            {   
                unset($team['id']);
                unset($team['person_id']);
                
                $person['teams'][] = $team;
            }
            // Plans
            $plansSql = 'SELECT * FROM person_plans WHERE person_id = :personId;';
            
            $plansStmt = $conn->prepare($plansSql);
            $plansStmt->execute(array('personId' => $personId));
            $plans = $plansStmt->fetchAll();
            foreach($plans as $plan)
            {   
                unset($plan['id']);
                unset($plan['person_id']);
                
                $person['plans'][] = $plan;
            }
            // Persons
            $personPersonsSql = 'SELECT * FROM person_persons WHERE parent_id = :personId ORDER BY role;';
            
            $personPersonsStmt = $conn->prepare($personPersonsSql);
            $personPersonsStmt->execute(array('personId' => $personId));
            $personPersons = $personPersonsStmt->fetchAll();
            foreach($personPersons as $personPerson)
            {   
                unset($personPerson['id']);
                unset($personPerson['parent_id']);
                
                $personPerson['childKey'] = $personKeys[$personPerson['child_id']];
                unset($personPerson['child_id']);
                
                $person['persons'][] = $personPerson;
            }
            // Feds
            $fedsSql = 'SELECT * FROM person_feds WHERE person_id = :personId;';
            $fedsStmt = $conn->prepare($fedsSql);
            $fedsStmt->execute(array('personId' => $personId));
            $feds = $fedsStmt->fetchAll();
            foreach($feds as $fed)
            {   
                $fedId = $fed['id'];
                
                unset($fed['id']);
                unset($fed['person_id']);
                
                $fed['certs'] = array();
                
                // Fed Certs
                $fedCertsSql = 'SELECT * FROM person_fed_certs WHERE person_fed_id = :fedId;';
                $fedCertsStmt = $conn->prepare($fedCertsSql);
                $fedCertsStmt->execute(array('fedId' => $fedId));
                $fedCerts = $fedCertsStmt->fetchAll();
                foreach($fedCerts as $fedCert)
                {
                    unset($fedCert['id']);
                    unset($fedCert['person_fed_id']);
                
                    $fed['certs'][] = $fedCert;
                }
                $person['feds'][] = $fed;
            }
            // Done
            $persons[] = $person;
        }
        return $persons;
    }
    protected function unloadUsers($conn)
    {   
        $usersx = $conn->fetchAll('SELECT * FROM users ORDER BY id');
        $users = array();
        foreach($usersx as $user)
        {
            unset($user['id']);
            
            $user['authens'] = array();
            
            $users[] = $user;
        }
        return $users;
    }
}
?>
