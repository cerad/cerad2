<?php

namespace Cerad\Bundle\GameBundle\Entity;

/* ==============================================
 * Each game has a project and a level
 * game.num is unique within project
 */
class GameTeam extends AbstractEntity
{
    const RoleHome = 'Home';
    const RoleAway = 'Away';
    const RoleSlot = 'Slot';
    
    const SlotHome = 1;
    const SlotAway = 2;

    protected $id;
    
    protected $slot;
    protected $role;
    
    protected $game;
    
    protected $team;
    protected $name;
    protected $group;
    
    protected $orgId;   // AKA orgKey
    protected $levelId; // Could be different than the game
    
    protected $score;
    protected $report;  // Misconduct etc, sendoff caution sportsmanship
    
    protected $status; // Really need?
    
    public function getId()      { return $this->id;      }
    public function getSlot()    { return $this->slot;    }
    public function getRole()    { return $this->role;    }
    public function getGame()    { return $this->game;    }
    public function getTeam()    { return $this->team;    }
    public function getName()    { return $this->name;    }
    public function getGroup()   { return $this->group;   }
    public function getOrgId()   { return $this->orgId;   }
    public function getOrgKey()  { return $this->orgId;   }
    public function getLevelId() { return $this->levelId; }
    public function getLevelKey(){ return $this->levelId; }
    public function getScore()   { return $this->score;   }
    public function getStatus()  { return $this->status;  }
    
    public function setSlot    ($value) { $this->onPropertySet('slot',    $value); }
    public function setRole    ($value) { $this->onPropertySet('role',    $value); }
    public function setGame    ($value) { $this->onPropertySet('game',    $value); }
    public function setTeam    ($value) { $this->onPropertySet('team',    $value); }
    public function setName    ($value) { $this->onPropertySet('name',    $value); }
    public function setGroup   ($value) { $this->onPropertySet('group',   $value); }
    public function setOrgId   ($value) { $this->onPropertySet('orgId',   $value); }
    public function setOrgKey  ($value) { $this->onPropertySet('orgId',   $value); }
    public function setLevelId ($value) { $this->onPropertySet('levelId', $value); }
    public function setLevelKey($value) { $this->onPropertySet('levelId', $value); }
    public function setScore   ($value) { $this->onPropertySet('score',   $value); }
    public function setStatus  ($value) { $this->onPropertySet('status',  $value); }
    
    public function getRoleForSlot($slot)
    {
        switch($slot)
        {
            case self::SlotHome: return self::RoleHome;
            case self::SlotAway: return self::RoleAway;
        }
        return self::RoleSlot . $slot;
    }
    /* ============================================================
     * Game Team Report
     * All stored in an array
     */
    public function createGameTeamReport($config = null) { return new GameTeamReport($config); }
    public function createPoolTeamReport($config = null) { return new PoolTeamReport($config); }
    
    public function getReport()  
    { 
        $report = new GameTeamReport($this->report);
        $report->setTeam($this);
        return $report;
    }
    public function setReport(GameTeamReport $report) 
    { 
        $this->onPropertySet('report',$report->getData()); 
    }
    /* ==========================================================
     * Cheesy clone
     */
    public $namex;
}
?>
