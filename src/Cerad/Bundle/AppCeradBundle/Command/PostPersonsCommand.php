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
class PostPersonsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_app:post:persons');
        $this->setDescription('Get Persons');
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $personData = array(
            'name' => array(
                'full'  => 'Newbie Full',
                'first' => 'Newbie First',
                'last'  => 'Newbie Last',
            ),
            'dob'      => '2010-11-12',
        );
        $personDataJson = json_encode($personData);
        
        // TODO: Load Buzz and see if this simplifies things
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://local.zayso.org/cerad2/api/v1/persons");
        
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS,$personDataJson);
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($personDataJson))                                                                       
        );
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                
        $results = curl_exec($curl);
        
      //$info = curl_getinfo($curl);
        
      //print_r($info); die();
        
        $httpCode    = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl,CURLINFO_CONTENT_TYPE);
        $location    = curl_getinfo($curl,CURLINFO_EFFECTIVE_URL);
        
        curl_close($curl);
        
        // Location: http://local.zayso.org/cerad2/api/v1/persons
        // Why is the id dropped?
        echo sprintf("Code: %d, %s %s\n",$httpCode,$contentType,$location);
        
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
