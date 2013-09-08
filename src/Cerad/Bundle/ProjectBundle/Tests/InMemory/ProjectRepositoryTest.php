<?php
namespace Cerad\Bundle\ProjectBundle\Tests\InMemory;

use Cerad\Bundle\ProjectBundle\Model\Project;
use Cerad\Bundle\ProjectBundle\Model\ProjectRepositoryInterface;

use Cerad\Bundle\ProjectBundle\InMemory\ProjectFind;
use Cerad\Bundle\ProjectBundle\InMemory\ProjectRepository;

class ProjectRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $projectId     = 'CeradTest13';
    
    protected static $repo;
    
    public static function setUpBeforeClass()
    {
        $files = array(
            __DIR__ . '/projects/Tests.yml',
        );
        
        self::$repo = new ProjectRepository($files);
    }
    public function testExistence()
    {   
        $repo = new ProjectRepository(array());
        $this->assertTrue($repo instanceOf ProjectRepositoryInterface);     
    }
    public function testFind()
    {
        $project = self::$repo->find($this->projectId);
        
        $this->assertTrue($project instanceOf Project);
        
        $this->assertEquals($this->projectId,$project->getId());
        
      //$this->assertEquals('ceradtest',          $project->getSlug());
      //$this->assertEquals('ceradtest2013',      $project->getSlugx());
        $this->assertEquals('Active',             $project->getStatus());
        $this->assertEquals('Yes',                $project->getVerified());
        $this->assertEquals('AYSO',               $project->getFedId());
        $this->assertEquals('AYSOV',              $project->getFedRoleId());
        $this->assertEquals('Cerad Test 13 2013', $project->getTitle());
        $this->assertEquals('USSF Cerad Test 2013 - Huntsville, Alabama - October 18, 19, 20', $project->getDesc());   
    }
    public function testFindAll()
    {
        $projects = self::$repo->findAll();
        $this->assertGreaterThanOrEqual(2,count($projects));
        
        $this->assertTrue(isset($projects[$this->projectId]));
    }
    public function testFindAllByStatus()
    {
        $projects = self::$repo->findAllByStatus('Completed');
        
        $this->assertGreaterThanOrEqual(1,count($projects));
        foreach($projects as $project)
        {
            $this->assertEquals('Completed',$project->getStatus());
        }
    }
    public function testFindBySlug()
    {
        $repo = self::$repo;
        
        $project1 = $repo->findBySlug('ceradtest');
        $this->assertNotNull($project1);
        $this->assertEquals($this->projectId,$project1->getId());
        
        $project2 = $repo->findBySlug('ceradtest2013');       
        $this->assertNotNull($project2);
        $this->assertEquals($this->projectId,$project2->getId());
    }
    public function testProjectFind()
    {
        $repo = self::$repo;
        
        $projectFind = new ProjectFind($repo,$this->projectId);
        
        $this->assertEquals($this->projectId,$projectFind->project->getId());
        
    }

}

?>
