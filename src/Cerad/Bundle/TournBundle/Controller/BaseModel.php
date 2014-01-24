<?php
namespace Cerad\Bundle\TournBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/* =====================================================
 * Model
 * RequestModel *
 * ControllerModel
 * ActionModel
 * 
 * Keep array access for backwards compatibility
 * 
 * Make properties public to allow direct access
 * 
 * Eventually this might be the pattern for a one model per action pattern
 */
class BaseModel implements \ArrayAccess
{
    public $_route;
    public $_redirect;
    public $_template;
    public $_response;
    
    public function __construct(Request $request)
    {
        $this->_route    = $request->get('_route');
        $this->_redirect = $request->get('_redirect');
        $this->_template = $request->get('_template');
    }
    public function offsetSet($offset, $value) 
    {
        $this->$offset = $value;
    }
    public function offsetUnset($offset) 
    {
        $this->$offset = null; // Good enough for now
    }
    public function offsetExists($offset) 
    {
        return property_exists($this,$offset);
    }
    public function offsetGet($offset) 
    {
        return property_exists($this,$offset) ? $this->$offset : null;
    }
}
?>
