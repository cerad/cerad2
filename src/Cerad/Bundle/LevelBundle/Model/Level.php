<?php
namespace Cerad\Bundle\LevelBundle\Model;

class Level
{
    protected $id;
    
    protected $name;
    protected $sport;
    protected $domain;
    protected $domainSub;
    
    protected $div;
    protected $age;
    protected $gender;
    
    protected $status = 'Active';
    
    public function getId()        { return $this->id;     }
    public function getKey()       { return $this->id;     }
    public function getName()      { return $this->name;   }
    public function getStatus()    { return $this->status; }
    
    public function getAge()       { return $this->age;     }
    public function getGender()    { return $this->gender;  }
    public function getProgram()   { return $this->div; }
    
    public function getSport()     { return $this->sport;  }
    public function getDomain()    { return $this->domain; }
    public function getDomainSub() { return $this->domainSub; }
    
    public function setId       ($value) { $this->onPropertySet('id',       $value); }
    public function setName     ($value) { $this->onPropertySet('name',     $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }
    
    public function setSport    ($value) { $this->onPropertySet('sport',    $value); }
    public function setDomain   ($value) { $this->onPropertySet('domain',   $value); }
    public function setDomainSub($value) { $this->onPropertySet('domainSub',$value); }
    
    protected function onPropertySet($name,$newValue) { $this->$name = $newValue; }
    
    public function __construct($config = null)
    {
        if (!$config) return;
        
        foreach($config as $name => $value) { $this->$name = $value; }
    }
}
?>
