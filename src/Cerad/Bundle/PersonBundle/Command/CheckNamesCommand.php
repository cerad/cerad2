<?php
namespace Cerad\Bundle\PersonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Cerad\Bundle\CoreBundle\Event\Person\ChangedProjectPersonEvent;

class CheckNamesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_person__check_names')
            ->setDescription('Check Names');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectKey = 'AYSONationalGames2014';
        
        $this->checkNames($projectKey);
        
      //$this->dispatchChangedProjectPersonEvent($projectKey);
        
        return; if ($input && $output);
    }
    protected function checkNames($projectKey)
    {
        $projectPersonRepo = $this->getService('cerad_person__project_person__repository');
        $projectPersons = $projectPersonRepo->findAllByProjectkey($projectKey);
        echo sprintf("ProjectPerson Count %d\n",count($projectPersons));
        
        $projectPersonNames = array();
        
        foreach($projectPersons as $projectPerson)
        {
            $person = $projectPerson->getPerson();
            $personName = $person->getName()->full;
            
            $projectPersonName = $projectPerson->getPersonName();
            
            if (!isset($projectPersonNames[$projectPersonName])) $projectPersonNames[$projectPersonName] = 1;
            else
            {
                $projectPersonNames[$projectPersonName]++;
                echo sprintf("Dup Project Person Name %s\n",$projectPersonName);
            }
            if ($personName != $projectPersonName)
            {
                echo sprintf("Name Mismatch '%s' '%s'\n",$personName,$projectPersonName);
                $projectPerson->setPersonName($personName);
            }
        }
        $projectPersonRepo->flush();
    }
    protected function dispatchChangedProjectPersonEvent($projectKey)
    {
        $projectPersonRepo = $this->getService('cerad_person__project_person__repository');
        $projectPersons = $projectPersonRepo->findAllByProjectkey($projectKey);
 
        $dispatcher = $this->getService('event_dispatcher');
        
        foreach($projectPersons as $projectPerson)
        {
            $event = new ChangedProjectPersonEvent($projectPerson);
            $dispatcher->dispatch(ChangedProjectPersonEvent::Changed,$event);
        }
    }
}
?>
