<?php

namespace Cerad\Bundle\LevelBundle\Tests\InMemory;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\LevelBundle\Model\Level;

class LevelRepositoryServiceTest extends WebTestCase
{
    protected static $client;
    protected static $container;
 
    public static function setUpBeforeClass()
    {
        self::$client    = static::createClient();
        self::$container = self::$client->getContainer();        
    }    
    public function testFiles()
    {
        $files = self::$container->getParameter('cerad_level_level_files');
        $this->assertTrue(is_array($files));
        $this->assertTrue(0 < count($files));
    }
    public function testRepo()
    {
        $repo = self::$container->get('cerad_level.level_repository');
        
        $level = $repo->find('AYSO_U14G_Core');
        
        $this->assertTrue($level instanceOf Level);
     }
}
