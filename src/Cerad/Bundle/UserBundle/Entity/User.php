<?php
namespace Cerad\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Cerad\Bundle\UserBundle\Model\User as UserModel;

class User extends UserModel
{       
    public function __construct()
    {
        parent::__construct();

        $this->identifiers = new ArrayCollection();
    }
    public function createFed($params = null) { return new PersonFed($params); }
    
    /* ======================================================
     * Value objects hydration stuff
     * Currently assume actual objects
     * Might be able to handle arrays as well
     * Instead of a prop list might be nice to implement array_keys
     */
    protected function dehydrate($prefix,$item)
    {
        foreach($item->propNames as $propName)
        {
            $propNameThis = $prefix . ucfirst($propName);
            
            if (property_exists($this,$propNameThis))
            {
                $this->$propNameThis = $item->$propName;
            }
        }
    }
    protected function hydrate($prefix,$item)
    {
        foreach($item->propNames as $propName)
        {
            $propNameThis = $prefix . ucfirst($propName);
            
            $item->$propName = property_exists($this,$propNameThis) ? $this->$propNameThis : null;
         }
    }
    public function onPrePersistOrUpdate()
    {
        // VOs
        $this->dehydrate('name',   $this->name);
        $this->dehydrate('address',$this->address);
    }
    public function onPostLoad()
    {
        $this->name = $this->createName();
        $this->hydrate('name',$this->name);
        
        $this->address = $this->createAddress();
        $this->hydrate('address',$this->address);
    }
}
?>
