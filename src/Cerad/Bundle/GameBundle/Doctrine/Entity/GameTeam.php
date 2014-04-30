<?php

namespace Cerad\Bundle\GameBundle\Doctrine\Entity;

/* ==============================================
 * GameTeam always belongs to a Game and thus have a project
 * The level of a game team could be different the the level of a game
 * 
 * The GroupKeySlot uniquely identifies a team within a game GroupKey
 * 
 * name is what is displayed
 * 
 * Need a soft link to a Team object somehow
 * Or possibly a hard link?
 */
class GameTeam
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
    protected $name;
    
    // Maybe
    protected $team;
    protected $coach;
    protected $teamNum;
    
    protected $orgKey;
    protected $levelKey;   // Could be different than the game
    protected $groupSlot;  // U10B A1, A2 etc
    
    protected $score;
    protected $report;  // Misconduct etc, sendoff caution sportsmanship injuries
    
    protected $status = 'Active'; // Really need?
    
    public function getId()        { return $this->id;        }
    public function getSlot()      { return $this->slot;      }
    public function getRole()      { return $this->role;      }
    public function getGame()      { return $this->game;      }
    public function getTeam()      { return $this->team;      }
  //public function getName()      { return $this->name;      }
    public function getLevelKey()  { return $this->levelKey;  }
    public function getGroupSlot() { return $this->groupSlot; }
    public function getScore()     { return $this->score;     }
    public function getStatus()    { return $this->status;    }
    
    public function setSlot     ($value) { $this->slot      = $value; }
    public function setRole     ($value) { $this->role      = $value; }
    public function setGame     ($value) { $this->game      = $value; }
    public function setTeam     ($value) { $this->team      = $value; }
    public function setName     ($value) { $this->name      = $value; }
    public function setLevelKey ($value) { $this->levelKey  = $value; }
    public function setGroupSlot($value) { $this->groupSlot = $value; }
    public function setScore    ($value) { $this->score     = $value; }
    public function setStatus   ($value) { $this->status    = $value; }
    
    public function getProjectKey() { return $this->game->getProjectKey(); }
    
    public function getName()      
    { 
        return $this->name ? $this->name : $this->groupSlot;      
    }
    public function getRoleForSlot($slot)
    {
        switch($slot)
        {
            case self::SlotHome: return self::RoleHome;
            case self::SlotAway: return self::RoleAway;
        }
        return self::RoleSlot . $slot;
    }
    /* ======================================================
     * Report is a value object
     */
    public function getReport()
    {
        return new GameTeamReport($this->report);
    }
    public function setReport($report)
    {
        $this->report = $report ? $report->getData() : null;
    }
}
?>
