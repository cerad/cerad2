<?php
namespace Cerad\Bundle\AppCeradBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/* =======================================================
 * See how big of a pain it is to insert/update using dbal
 */
class UpdatePersonsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('cerad_app:update:persons')
            ->setDescription('Update Persons');
          //->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
          //->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
        
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getService('doctrine.dbal.default_connection');
        
        $conn->executeUpdate('DELETE FROM persons;' );
        $conn->executeUpdate('ALTER TABLE persons AUTO_INCREMENT = 1;');        
        
        $personInsertSql = <<<EOT
INSERT INTO persons 
(guid,name_full,status,dob)
VALUES
(:guid,:nameFull,:status,:dob)
EOT;
        $personInsertStatement = $conn->prepare($personInsertSql);
        
        $person1 = array('guid' => 'GUID 1','nameFull' => 'Art 01', 'status' => 'Active', 'dob' => '1958-06-05');
        $person2 = array('guid' => 'GUID 2','nameFull' => "Art '02", 'status' => 'Active','dob' => null);
        
        $count = $personInsertStatement->execute($person1);
        $count = $personInsertStatement->execute($person2);
        
        $personId = $conn->lastInsertId();
        
        echo sprintf("Inserted %d %d\n",$count,$personId);
        
        return;
    }
}
?>
