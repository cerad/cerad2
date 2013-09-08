<?php

namespace Cerad\Bundle\ProjectBundle\Tests\InMemory;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectRepositoryServiceTest extends WebTestCase
{
    protected static $client;
    protected static $container;
 
    public static function setUpBeforeClass()
    {
        self::$client    = static::createClient();
        self::$container = self::$client->getContainer();        
    }    
    public function testLists()
    {
        $files = self::$container->getParameter('cerad_project_project_files');
        $this->assertTrue(is_array($files));
        $this->assertTrue(0 < count($files));
    }
    public function testRepo()
    {
        $repo = self::$container->get('cerad_project.repository.in_memory');
        $projectId = self::$container->getParameter('cerad_project_project_default');
        
        $project = $repo->find($projectId);
        $this->assertEquals($projectId,$project->getId());
    }
    public function testFind()
    {
        $find = self::$container->get('cerad_project.find_default.in_memory');
        $projectId = self::$container->getParameter('cerad_project_project_default');
        
        $this->assertEquals($projectId,$find->project->getId());
    }
}
