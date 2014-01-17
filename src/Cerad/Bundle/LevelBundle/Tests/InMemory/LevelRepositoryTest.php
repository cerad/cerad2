<?php
namespace Cerad\Bundle\LevelBundle\Tests\InMemory;

use Cerad\Bundle\LevelBundle\Model\Level;
use Cerad\Bundle\LevelBundle\Model\LevelRepositoryInterface;

use Cerad\Bundle\LevelBundle\InMemory\LevelRepository;

class LevelRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $repo;
    
    public function setUp()
    {
        $dir = __DIR__ . '/../../Resources/config/levels/';
        
        $files = array(
            $dir . 'ayso_core.yml',
            $dir . 'ayso_extra.yml',
            $dir . 'ayso_league.yml',
            $dir . 'ayso_allstars.yml',
        );
        
        $this->repo = new LevelRepository($files);
    }
    public function testExistence()
    {   
        $repo = new LevelRepository(array());
        
        $this->assertTrue($repo instanceOf LevelRepositoryInterface);     
    }
    public function testFind()
    {        
        $repo = $this->repo;
        
        $level = $repo->find('AYSO_U14G_Core');
        
        $this->assertTrue($level instanceOf Level);
        
        $this->assertEquals('U14', $level->getAge());
        $this->assertEquals('G',   $level->getGender());
        $this->assertEquals('Core',$level->getProgram());
        
    }
    public function testQuery()
    {   
        $repo = $this->repo;
        
        $params1 = array();
        $params1['programs'] = 'League';
        $keys1 = $repo->queryKeys($params1);
        $this->assertEquals(10, count($keys1));
        $this->assertEquals('AYSO_U12G_League', $keys1[3]);
        
        $params2 = array();
        $params2['programs'] = array('League','Core');
        
        $keys2 = $repo->queryKeys($params2);
        $this->assertEquals(20, count($keys2));
        
        $params3 = array();
        $params3['programs'] = 'Core';
        $params3['genders'] = array('G');
        $keys3 = $repo->queryKeys($params3);
        $this->assertEquals(5, count($keys3));
        $this->assertEquals('AYSO_U16G_Core', $keys3[3]);
        
    }
}

?>
