<?php
namespace Cerad\Bundle\GameBundle\Doctrine\Entity;

//  Doctrine\Common\Collections\ArrayCollection;

class Team
{
    const RolePhysical = 'Physical';
    
    protected $id;
    protected $key;
    
    protected $orgKey;
    protected $levelKey;
    protected $projectKey;
    
    protected $num;
    protected $role = self::RolePhysical;
    protected $name;
    protected $coach;
    
    protected $points; // Soccerfest points
    
    protected $status = 'Active';
   
    public function getId()      { return $this->id;      }
    public function getKey()     { return $this->key;     }
    public function getNum()     { return $this->num;     }
    public function getRole()    { return $this->role;    }
    public function getName()    { return $this->name;    }
    public function getCoach()   { return $this->coach;   }
    public function getPoints()  { return $this->points;  }
    public function getStatus()  { return $this->status;  }
    
    public function getOrgKey()     { return $this->orgKey;     }
    public function getLevelKey()   { return $this->levelKey;   }
    public function getProjectKey() { return $this->projectKey; }
    
    public function setNum      ($value) { $this->num    = $value; }
    public function setRole     ($value) { $this->role   = $value; }
    public function setName     ($value) { $this->name   = $value; }
    public function setCoach    ($value) { $this->coach  = $value; }
    public function setPoints   ($value) { $this->points = $value; }
    public function setStatus   ($value) { $this->status = $value; }
    
    public function setKey       ($value) { $this->key        = $value; }
    public function setOrgKey    ($value) { $this->orgKey     = $value; }
    public function setLevelKey  ($value) { $this->levelKey   = $value; }
    public function setProjectKey($value) { $this->projectKey = $value; }
    
    public function __construct()
    {
    }
    /* ==============================================
     * Readable view of the team?
     * Currently used to select teams
     * Really would like to move this out of here
     */
    public function getDesc()
    {
        $levelKeyParts = explode('_',$this->levelKey);
        
        return sprintf('%s %s',$levelKeyParts[1],$this->name);
    }
}
?>
