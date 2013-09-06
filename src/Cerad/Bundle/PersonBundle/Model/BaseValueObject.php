<?php
namespace Cerad\Bundle\PersonBundle\Model;

/* ====================================================================
 * See if majic can be used to limit access to only declared properties
 */
class BaseValueObject
{
    public $propNames;
    
    public function hydrate($config)
    {
        // Should be cleaner
        if (!is_array($config) && !($config instanceOf \ArrayAccess)) return false;
       
        foreach($this->propNames as $propName)
        {
            $this->$propName = isset($config[$propName]) ? $config[$propName] : null;
        }
        return true;
    }
}

?>
