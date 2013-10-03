<?php
namespace Cerad\Bundle\LevelBundle\Tests\InMemory;

use Cerad\Bundle\LevelBundle\Model\Level;
use Cerad\Bundle\LevelBundle\Model\LevelRepositoryInterface;

use Cerad\Bundle\LevelBundle\InMemory\LevelRepository;

class LevelRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testExistence()
    {   
        $repo = new LevelRepository(array());
        
        $this->assertTrue($repo instanceOf LevelRepositoryInterface);     
    }
    public function testFind()
    {
        $files = array(__DIR__ . '/../../Resources/config/levels/ayso_core.yml');
        
        $repo = new LevelRepository($files);
        
        $level = $repo->find('AYSO_U14G_Core');
        
        $this->assertTrue($level instanceOf Level);
        
        return;
        
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
}

?>
