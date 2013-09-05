<?php
/* ===========================================================
 * This is currently service based but should also have
 * a standalone yaml file somewhere
 */
namespace Cerad\Bundle\ProjectBundle\Tests\ProjectRepository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\ProjectBundle\Model\Project;
use Cerad\Bundle\ProjectBundle\Model\ProjectRepositoryInterface;

use Cerad\Bundle\ProjectBundle\Repository\ProjectFind;
use Cerad\Bundle\ProjectBundle\Repository\ProjectInMemoryRepository;

class InMemoryTest extends WebTestCase
{
    protected $projectId     = 'CeradTest13';
    protected $repoServiceId = 'cerad_project.repository.in_memory';
    
    protected function getRepo()
    {
       $client = static::createClient();

       $repo = $client->getContainer()->get($this->repoServiceId);
       
       return $repo;
       
    }
    public function testExistence()
    {
        $repo = $this->getRepo();
        $this->assertTrue($repo instanceOf ProjectRepositoryInterface);
        $this->assertTrue($repo instanceOf ProjectInMemoryRepository);        
    }
    public function testFind()
    {
        $repo = $this->getRepo();
        
        $project = $repo->find($this->projectId);
        
        $this->assertTrue($project instanceOf Project);
        
        $this->assertEquals($this->projectId,$project->getId());
        
        $this->assertEquals('ceradtest',          $project->getSlug());
        $this->assertEquals('ceradtest2013',      $project->getSlugx());
        $this->assertEquals('Active',             $project->getStatus());
        $this->assertEquals('Yes',                $project->getVerified());
        $this->assertEquals('AYSO',               $project->getFedId());
        $this->assertEquals('AYSOV',              $project->getFedOffId());
        $this->assertEquals('Cerad Test 13 2013', $project->getTitle());
        $this->assertEquals('USSF Cerad Test 2013 - Huntsville, Alabama - October 18, 19, 20', $project->getDesc());
        
    }
    public function testFindAll()
    {
        $repo = $this->getRepo();
        
        $projects = $repo->findAll();
        $this->assertGreaterThanOrEqual(2,count($projects));
        
        $this->assertTrue(isset($projects[$this->projectId]));
    }
    public function testFindAllByStatus()
    {
        $repo = $this->getRepo();
        
        $projects = $repo->findAllByStatus('Completed');
        
        $this->assertGreaterThanOrEqual(1,count($projects));
        foreach($projects as $project)
        {
            $this->assertEquals('Completed',$project->getStatus());
        }
    }
    public function testFindBySlug()
    {
        $repo = $this->getRepo();
        
        $project = $repo->findBySlug('ceradtest');       
        $this->assertEquals($this->projectId,$project->getId());
        
        $projectx = $repo->findBySlug('ceradtest2013');       
        $this->assertEquals($this->projectId,$projectx->getId());
    }
    public function testProjectFind()
    {
        $repo = $this->getRepo();
        
        $projectFind = new ProjectFind($repo,$this->projectId);
        
        $this->assertEquals($this->projectId,$projectFind->project->getId());
        
    }
    public function testProjectFindService()
    {
        $client = static::createClient();

        $projectFind = $client->getContainer()->get('cerad_project.find_default.in_memory');
        
        $this->assertEquals($this->projectId,$projectFind->project->getId());
        
    }
}
