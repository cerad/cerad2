<?php
namespace Cerad\Library\CommonLibrary\Tests;

use Cerad\Library\CommonLibrary\Functions\Guid;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testGuid()
    {
        $id = Guid::gen();
        
        $this->assertEquals(36,strlen($id));
    }
}

?>
