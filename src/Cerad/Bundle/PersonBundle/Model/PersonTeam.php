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
    
    protected $teamKey;
    protected $teamName;
    protected $teamDesc;
    
    protected $levelKey;
    protected $projectKey;
    
    protected $status = 'Active';
    
    public function getTeamKey ()   { return $this->teamKey;    }
    public function getTeamName()   { return $this->teamName;   }
    public function getTeamDesc()   { return $this->teamDesc;   }
    
    public function getRole    ()   { return $this->role;       }
    public function getPerson  ()   { return $this->person;     }
    public function getStatus  ()   { return $this->status;     }
    public function getLevelKey()   { return $this->levelKey;   }
    public function getProjectKey() { return $this->projectKey; }
    
    // Don't really need team setters
    public function setTeamKey ($v) { $this->teamKey  = $v; return $this; }
    public function setTeamName($v) { $this->teamName = $v; return $this; }
    public function setTeamDesc($v) { $this->teamDesc = $v; return $this; }
    
    public function setRole    ($v)   { $this->role       = $v; return $this; }
    public function setPerson  ($v)   { $this->person     = $v; return $this; }
    public function setStatus  ($v)   { $this->status     = $v; return $this; }
    public function setLevelKey($v)   { $this->levelKey   = $v; return $this; }
    public function setProjectKey($v) { $this->projectKey = $v; return $this; }
    
    public function setTeam($team)
    {
        if (!$team)
        {
            // This reall should not happen, clearing a team should delete the whole entry
            $this->teamKey  = null;
            $this->teamName = null;
            $this->teamDesc = null;
            $this->levelKey = null;
            $this->projectKey = null; 
            return;
        }
        $this->teamKey    = $team->getKey();
        $this->teamName   = $team->getName();
        $this->teamDesc   = $team->getDesc();
        $this->levelKey   = $team->getLevelKey();
        $this->projectKey = $team->getProjectKey();
    }
    public function __construct() {}
}
?>
