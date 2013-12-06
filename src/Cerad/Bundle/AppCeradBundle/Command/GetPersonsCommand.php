<?php
namespace Cerad\Bundle\AppCeradBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/* =======================================================
 * Get a list of people via api
 */
class GetPersonsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_app:get:persons');
        $this->setDescription('Get Persons');
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://local.zayso.org/cerad2/api/v1/persons?page=5&per_page=3&rep=min");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                
        $results = curl_exec($curl);
        
        $httpCode    = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl,CURLINFO_CONTENT_TYPE);
        
        curl_close($curl);
        
        echo sprintf("Code: %d, %s\n",$httpCode,$contentType);
        
        // Assume for now have the majic security string
        // )]}',\n
        $items = json_decode(substr($results,6),true);
        
        print_r($items);
    }  
    /*
     [1] => stdClass Object
        (
            [id] => 136
            [guid] => 0A3F8777-3C9D-4C7F-80E4-90349EDF9CA2
            [name_full] => Barry Moser
            [name_first] => Barry
            [name_last] => Moser
            [dob] =>
        )
     */
}
?>
