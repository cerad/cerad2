<?php
namespace Cerad\Bundle\OrgBundle\Entity;

class Org extends AbstractEntity
{
    protected $id;     // AYSOR0123
    protected $parent; // AYSOA05C
    
    protected $city;
    protected $state;
    protected $status = 'Active';

    
    public function getId()        { return $this->id;        }
    public function getParent()    { return $this->parent;    }
    
    public function getCity()      { return $this->city;      }
    public function getState()     { return $this->state;     }
    public function getStatus()    { return $this->status;    }
    
    public function setId       ($value) { $this->onPropertySet('id',       $value); }
    public function setParent   ($value) { $this->onPropertySet('parent',   $value); }
    public function setCity     ($value) { $this->onPropertySet('city',     $value); }
    public function setState    ($value) { $this->onPropertySet('state',    $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }   
    
    public function __construct($config = null)
    {
        if (!$config) return;
        
        foreach($config as $name => $value) { $this->$name = $value; }
    }
}
?>
