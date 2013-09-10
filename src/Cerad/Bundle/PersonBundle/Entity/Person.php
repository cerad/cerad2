<?php
namespace Cerad\Bundle\PersonBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Cerad\Bundle\PersonBundle\Model\Person as PersonModel;

use Cerad\Bundle\PersonBundle\Entity\PersonFed;
use Cerad\Bundle\PersonBundle\Entity\PersonPlan;
use Cerad\Bundle\PersonBundle\Entity\PersonPerson;

class Person extends PersonModel
{   
    /* =========================================
     * Value objects
     */
    private $nameFull;
    private $nameFirst;
    private $nameLast;
    private $nameNick;
    private $nameMiddle;
    
    private $addressCity;
    private $addressState;
    private $addressZipcode;
    
    public function __construct()
    {
        parent::__construct();

        $this->feds    = new ArrayCollection(); 
        $this->plans   = new ArrayCollection();
        $this->persons = new ArrayCollection(); 
    }
    public function createFed ($params = null) { return new PersonFed ($params); }
    public function createPlan($params = null) { return new PersonPlan($params); }
    
    public function createPersonPerson($params = null) { return new PersonPerson($params); }
    
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
