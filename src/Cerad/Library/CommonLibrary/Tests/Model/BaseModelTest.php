<?php
namespace Cerad\Library\CommonLibrary\Tests\Util;

use Cerad\Library\CommonLibrary\Model\BaseModel;

class TestModel extends BaseModel
{
    protected $id;
    public function __construct()
    {
        $this->id = $this->genId();
    }
    public function getId() { return $this->id; }
}
class BaseModelTest extends \PHPUnit_Framework_TestCase
{
    // Won't work, protected, need to derive a class
    public function testGuid()
    {
        $test = new TestModel();
        $this->assertEquals(36,strlen($test->getId()));
    }
    /* ================================================
     * Now we need some onPropertSet tests
     * In order to validate expected behaviours
     */
}

?>
