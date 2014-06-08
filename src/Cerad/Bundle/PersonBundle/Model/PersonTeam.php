<?php

namespace Cerad\Bundle\PersonBundle\Model;

/* =================================================
 * 08 June 2014
 * A person can be related to zero or more project teams
 */
class PersonTeam
{
    const RoleHeadCoach  = 'HeadCoach';
    const RoleAsstCoach  = 'AsstCoach';
    const RoleManager    = 'Manager';
    
    const RoleParent   = 'Parent';
    const RolePlayer   = 'Player';
    const RoleSpec     = 'Spectator';
    
    const RoleConflict = 'Conflict';
    const RoleBlocked  = 'Blocked'; // ByPerson, ByTeam, ByAdmin
    const RoleBlockedByPerson  = 'BlockedByPerson'; // ByPerson, ByTeam, ByAdmin
    
    protected $role;
    
    protected $person;
    
    protected $num;
    protected $name;
    
    protected $levelKey;
    protected $projectKey;
    
    protected $status = 'Active';
    
    public function getNum     ()   { return $this->num;        }
    public function getName    ()   { return $this->name;       }
    public function getRole    ()   { return $this->role;       }
    public function getPerson  ()   { return $this->person;     }
    public function getStatus  ()   { return $this->status;     }
    public function getLevelKey()   { return $this->levelKey;   }
    public function getProjectKey() { return $this->projectKey; }
    
    public function setNum     ($v)   { $this->num        = $v; return $this; }
    public function setName    ($v)   { $this->name       = $v; return $this; }
    public function setRole    ($v)   { $this->role       = $v; return $this; }
    public function setPerson  ($v)   { $this->person     = $v; return $this; }
    public function setStatus  ($v)   { $this->status     = $v; return $this; }
    public function setLevelKey($v)   { $this->levelKey   = $v; return $this; }
    public function setProjectKey($v) { $this->projectKey = $v; return $this; }
    
    
}
?>
