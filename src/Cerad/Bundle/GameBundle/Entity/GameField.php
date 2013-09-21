<?php
namespace Cerad\Bundle\GameBundle\Entity;

class GameField extends AbstractEntity
{
    protected $id;
    
    protected $name;
    protected $venue;
    protected $status = 'Active';
    protected $projectId;
    
    public function getId()        { return $this->id;        }
    public function getName()      { return $this->name;      }
    public function getVenue()     { return $this->venue;     }
    public function getStatus()    { return $this->status;    }
    public function getProjectId() { return $this->projectId; }
    
    public function setId       ($value) { $this->onPropertySet('id',       $value); }
    public function setName     ($value) { $this->onPropertySet('name',     $value); }
    public function setVenue    ($value) { $this->onPropertySet('venue',    $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }   
    public function setProjectId($value) { $this->onPropertySet('projectId',$value); }
    
    public function __construct($config = null)
    {
        if (!$config) return;
        
        foreach($config as $name => $value) { $this->$name = $value; }
    }
}
?>
