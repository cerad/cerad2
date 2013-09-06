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
        $oldValue = $this->$name;
        
        // If both are objects
        if (is_object($oldValue) && is_object($newValue))
        {
            // Same instance, trigger a clone to prevent side effects
            // No change trigger
            if ($oldValue === $newValue) 
            {
                // Copies parameters only
                // Then calls __clone();
                $this->$name = clone $newValue;
                return;
            }
            // Check props and same class
            if ($oldValue == $newValue)
            {
                // Same values but new object
                // Save but don't trigger a change
                $this->$name = $newValue;
                return;
            }
        }
        // At least one is not an object
        if ($this->$name === $newValue) return;
        
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
    protected function genId() 
    { 
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', 
            mt_rand(0,     65535), mt_rand(0,     65535), mt_rand(0, 65535), 
            mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), 
            mt_rand(0,     65535), mt_rand(0,     65535));  
    }
    /* ==========================================
     * Not really sure if this shoud go here or not
     * It's basically for database versioning
     * But it might be useful after commits and stuff
     */
    protected $version;
}
?>
