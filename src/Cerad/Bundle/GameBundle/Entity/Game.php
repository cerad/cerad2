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
    protected $link;   // Maybe to link crews?
    
    protected $dtBeg; // DateTime begin
    protected $dtEnd; // DateTime end
    
    protected $orgId;
    protected $field;
    protected $levelId;
    protected $projectId;
    
    protected $status = 'Active';
    
    protected $report;
    protected $reportStatus;
    
    protected $teams;
    protected $officials;
    
    public function getId()      { return $this->id;      }
    public function getNum()     { return $this->num;     }
    public function getRole()    { return $this->role;    }
    public function getGroup()   { return $this->group;   }
    public function getLink()    { return $this->link;    }
    public function getDtBeg()   { return $this->dtBeg;   }
    public function getDtEnd()   { return $this->dtEnd;   }
    public function getStatus()  { return $this->status;  }
    
    public function getOrgId()     { return $this->orgId;     }
    public function getField()     { return $this->field;     }
    public function getLevelId()   { return $this->levelId;   }
    public function getProjectId() { return $this->projectId; }
    
    public function getReport()       { return $this->report;       }
    public function getReportStatus() { return $this->reportStatus; }
    
    public function setNum      ($value) { $this->onPropertySet('num',      $value); }
    public function setLink     ($value) { $this->onPropertySet('link',     $value); }
    public function setRole     ($value) { $this->onPropertySet('role',     $value); }
    public function setGroup    ($value) { $this->onPropertySet('group',    $value); }
    public function setField    ($value) { $this->onPropertySet('field',    $value); }
    public function setDtBeg    ($value) { $this->onPropertySet('dtBeg',    $value); }
    public function setDtEnd    ($value) { $this->onPropertySet('dtEnd',    $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }
    
    public function setOrgId    ($value) { $this->onPropertySet('orgId',    $value); }
    public function setLevelId  ($value) { $this->onPropertySet('levelId',  $value); }
    public function setProjectId($value) { $this->onPropertySet('projectId',$value); }
    
    public function setReport      ($value) { $this->onPropertySet('report',      $value); }
    public function setReportStatus($value) { $this->onPropertySet('reportStatus',$value); }
    
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
    public function createGameOfficials($config = null) { return new GameOfficial($config); }
   
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
    }
    // Autocreate does not really make sense here
    public function getOfficialForSlot($slot)
    {
        if (isset($this->officials[$slot])) return $this->officials[$slot];
        return null;
    }
}
?>
