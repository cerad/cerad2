<?php
namespace Cerad\Bundle\PersonBundle\Model;

/* ==================================================
 * Was hping to avoid this but no easy way to inject this sort of stuff
 * Maybe a trait sometime
 * 
 * Besides, might be nice to be able to listen for this stuff at the model level
 */
use Doctrine\Common\NotifyPropertyChanged,
    Doctrine\Common\PropertyChangedListener;

class BaseModel implements NotifyPropertyChanged
{
    /* ========================================================================
     * Property change stuff
     */
    protected $listeners = array();

    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        $this->listeners[] = $listener;
    }    
    protected function onPropertyChanged($propName, $oldValue = null, $newValue = null)
    {
        foreach ($this->listeners as $listener) 
        {
            $listener->propertyChanged($this, $propName, $oldValue, $newValue);
        }
    }
    /* ===============================================
     * TODO: Verify value objects work properly
     */
    protected function onPropertySet($name,$newValue)
    {
        if ($this->$name === $newValue) return;
        
        $oldValue = $this->$name;
        
        $this->$name = $newValue;
        
        $this->onPropertyChanged($name,$oldValue,$newValue);    
    }
    /* ========================================================
     * Want to track just to make sure have a good id when persisting
     * The reason for shortening the guids is to make the url's prettier
     * Integers would also be a bit faster
     * 
     * Probably not very good reasons.
     */
    protected $isIdNew = false;
    protected function genId($prefix) 
    { 
        $this->isIdNew = true;
        return $prefix . rand(1000000,9999999);     
    }
    public function isIdNew() { return $this->isIdNew; }

}
?>
