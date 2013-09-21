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
    
    protected $status;
    
    protected $link;   // Future, allow linking the same level across multiple domains
    
    public function getId()        { return $this->id;     }
    public function getName()      { return $this->name;   }
    public function getLink()      { return $this->link;   }
    public function getStatus()    { return $this->status; }
    
    public function getSport()     { return $this->sport;  }
    public function getDomain()    { return $this->domain; }
    public function getDomainSub() { return $this->domainSub; }
    
    public function setId       ($value) { $this->onPropertySet('id',       $value); }
    public function setName     ($value) { $this->onPropertySet('name',     $value); }
    public function setLink     ($value) { $this->onPropertySet('link',     $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }
    
    public function setSport    ($value) { $this->onPropertySet('sport',    $value); }
    public function setDomain   ($value) { $this->onPropertySet('domain',   $value); }
    public function setDomainSub($value) { $this->onPropertySet('domainSub',$value); }
    
    protected function onPropertySet($name,$newValue) { $this->$name = $newValue; }
    
    public function __construct($config = null)
    {
        if (!$config) return;
        
        foreach($config as $name => $value)
        {
            $this->$name = $value;
        }
    }
}
?>
