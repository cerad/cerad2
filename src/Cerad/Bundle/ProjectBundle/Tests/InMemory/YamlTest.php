<?php
namespace Cerad\Bundle\ProjectBundle\Tests\InMemory;

use Symfony\Component\Yaml\Yaml;

/* ===============================================
 * Not so mich an actual test but just something to
 * see how the yaml stuff works
 */
class YamlTest extends \PHPUnit_Framework_TestCase
{
    public function test1()
    {
        $file = __DIR__ . '/projects/Tests.yml';
      //$file = __DIR__ . '/projects/AYSONationalGames2014.yml';
      //$file = __DIR__ . '/projects/USSF_AL_HFC_Kicks2013.yml';
        
        $configs = Yaml::parse(file_get_contents($file));
        foreach($configs as $projectId => $config)
        {
            if (0) echo sprintf("\nProject %s %s\n",$projectId,$config['info']['title']);
        }
    }
}
?>
