<?php
namespace Cerad\Library\CommonLibrary\Tests\Util;

use Cerad\Library\CommonLibrary\Util\GuidUtil;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testGuid()
    {
        $id = GuidUtil::gen();
        
        $this->assertEquals(36,strlen($id));
    }
}

?>
