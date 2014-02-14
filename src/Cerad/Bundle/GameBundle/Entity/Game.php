<?php
namespace Cerad\Bundle\GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/* ==============================================
 * Each game has a project and a level
 * game.num is unique within project
 */
class Game extends AbstractEntity
{
    const RoleGame = 'Game';

    protected $id;
    
    protected $num;   // Unique within project
    protected $role = self::RoleGame;
    protected $group;  // == play? == group?
    protected $groupType; // Pool Play, semi final etc
    protected $link;   // Maybe to link crews?
    
    protected $dtBeg; // DateTime begin
    protected $dtEnd; // DateTime end
    
    protected $orgId;     // AKA orgKey
    protected $field;
    protected $levelId;   // AKA levelKey
    protected $projectId; // AKA projectKey
    
    protected $status = 'Active';
    
    protected $report; // Array of properties
    
    protected $teams;
    protected $officials;
    
    public function getId()        { return $this->id;        }
    public function getNum()       { return $this->num;       }
    public function getRole()      { return $this->role;      }
    public function getGroup()     { return $this->group;     }
    public function getGroupType() { return $this->groupType; }
    public function getLink()      { return $this->link;      }
    public function getDtBeg()     { return $this->dtBeg;     }
    public function getDtEnd()     { return $this->dtEnd;     }
    public function getStatus()    { return $this->status;    }
    
    public function getOrgId()      { return $this->orgId;     }
    public function getOrgKey()     { return $this->orgId;     }
    public function getField()      { return $this->field;     }
    public function getLevelId()    { return $this->levelId;   }
    public function getLevelKey()   { return $this->levelId;   }
    public function getProjectId()  { return $this->projectId; }
    public function getProjectKey() { return $this->projectId; }
    
    public function setNum      ($value) { $this->onPropertySet('num',      $value); }
    public function setLink     ($value) { $this->onPropertySet('link',     $value); }
    public function setRole     ($value) { $this->onPropertySet('role',     $value); }
    public function setGroup    ($value) { $this->onPropertySet('group',    $value); }
    public function setGroupType($value) { $this->onPropertySet('groupType',$value); }
    public function setField    ($value) { $this->onPropertySet('field',    $value); }
    public function setDtBeg    ($value) { $this->onPropertySet('dtBeg',    $value); }
    public function setDtEnd    ($value) { $this->onPropertySet('dtEnd',    $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }
    
    public function setOrgId     ($value) { $this->onPropertySet('orgId',    $value); }
    public function setOrgKey    ($value) { $this->onPropertySet('orgId',    $value); }
    public function setLevelId   ($value) { $this->onPropertySet('levelId',  $value); }
    public function setLevelKey  ($value) { $this->onPropertySet('levelId',  $value); }
    public function setProjectId ($value) { $this->onPropertySet('projectId',$value); }
    public function setProjectKey($value) { $this->onPropertySet('projectId',$value); }
    
    /* =======================================
     * Create factory
     * Too many parameters
     */
    public function __construct()
    {
        $this->teams     = new ArrayCollection();
        $this->officials = new ArrayCollection();
    }
    /* =======================================
     * Team stuff
     */
   public function createGameTeam($config = null) { return new GameTeam($config); }
   
   public function getTeams($sort = true) 
    { 
        if (!$sort) return $this->teams;
        
        $items = $this->teams->toArray();
        
        ksort ($items);
        return $items; 
    }
    public function addTeam($team)
    {
        $this->teams[$team->getSlot()] = $team;
        
        $team->setGame($this);
        
        $this->onPropertyChanged('teams');
    }
    public function getTeamForSlot($slot,$autoCreate = true)
    {
        if (isset($this->teams[$slot])) return $this->teams[$slot];
        
        if (!$autoCreate) return null;
        
        $gameTeam = $this->createGameTeam();
        $gameTeam->setSlot($slot);
        $role = $gameTeam->getRoleForSlot($slot);
        $gameTeam->setRole($role);
        
        $this->addTeam($gameTeam);
        return $gameTeam;
    }
    public function getHomeTeam($autoCreate = true) { return $this->getTeamForSlot(GameTeam::SlotHome,$autoCreate); }
    public function getAwayTeam($autoCreate = true) { return $this->getTeamForSlot(GameTeam::SlotAway,$autoCreate); }
    
    /* =======================================
     * Officials
     */
    public function createGameOfficial($config = null) { return new GameOfficial($config); }
   
    public function getOfficials($sort = true) 
    { 
        if (!$sort) return $this->persons;
        
        $items = $this->officials->toArray();
        
        ksort ($items);
        return $items; 
    }
    public function addOfficial($official)
    {
        $this->officials[$official->getSlot()] = $official;
        
        $official->setGame($this);
        
        $this->onPropertyChanged('officials');
    }
    // Autocreate does not really make sense here
    public function getOfficialForSlot($slot)
    {
        if (isset($this->officials[$slot])) return $this->officials[$slot];
        return null;
    }
    /* ============================================================
     * Game Team Report
     * All stored in an array
     */
    public function createGameReport($config = null) { return new GameReport($config); }
    
    public function getReport()  
    { 
        $report = new GameReport($this->report);
        $report->setGame($this);
        return $report;
    }
    public function setReport(GameReport $report) 
    { 
        $this->onPropertySet('report',$report->getData()); 
    }
    // Try this
    public function getDateBegin() { return $this->dtBeg; }
    public function getTimeBegin() { return $this->dtBeg; }
    
    // Need to use interval to adjust ending info as well
    public function setDateBegin($value) { $this->onPropertySet('dtBeg',$value); }
    public function setTimeBegin($value) { $this->onPropertySet('dtBeg',$value); }
}
?>
