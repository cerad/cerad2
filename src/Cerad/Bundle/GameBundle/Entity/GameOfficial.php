<?php
namespace Cerad\Bundle\GameBundle\Entity;

/* ========================================================
 * Need a person value object
 */
class GameOfficial extends AbstractEntity
{
    protected $id;
    
    protected $game;
    protected $slot; // 1-5 for arbiter
    protected $role; // Referee, AR1 etc
    
    protected $personNameFull;
    protected $personNameLast;
    protected $personNameFirst;
    protected $personEmail;
    protected $personPhone;
    protected $personBadge;
    protected $personGuid;
    protected $personFedId;
    protected $personOrgId;
    
    protected $report;
    protected $status = 'Active';
   
    protected $state;           // Workflow
    protected $stateUpdatedOn;
    protected $stateUpdatedBy;
    
    
    public function getId  () { return $this->id;     }
    public function getGame() { return $this->game;   }
    public function getSlot() { return $this->slot;   }
    public function getRole() { return $this->role;   }
    
    public function getPersonNameFull () { return $this->personNameFull;  }
    public function getPersonNameLast () { return $this->personNameLast;  }
    public function getPersonNameFirst() { return $this->personNameFirst; }
    public function getPersonEmail    () { return $this->personEmail;     }
    public function getPersonPhone    () { return $this->personPhone;     }
    public function getPersonBadge    () { return $this->personBadge;     }
    public function getPersonGuid     () { return $this->personGuid;      }
    public function getPersonFedId    () { return $this->personFedId;     }
    public function getPersonOrgId    () { return $this->personOrgId;     }
    
    public function getReport()          { return $this->report;          }
    public function getStatus()          { return $this->status;          }
    
    public function getState()          { return $this->state;          }
    public function getStateUpdatedOn() { return $this->stateUpdatedOn; }
    public function getStateUpdatedBy() { return $this->stateUpdatedBy; }

    public function setGame($value) { $this->onPropertySet('game',  $value); }
    public function setSlot($value) { $this->onPropertySet('slot',  $value); } 
    public function setRole($value) { $this->onPropertySet('role',  $value); }
    
    public function setPersonNameFull ($value) { $this->onPropertySet('personNameFull', $value); }
    public function setPersonNameLast ($value) { $this->onPropertySet('personNameLast', $value); }
    public function setPersonNameFirst($value) { $this->onPropertySet('personNameFirst',$value); }
    public function setPersonEmail    ($value) { $this->onPropertySet('personEmail',    $value); }
    public function setPersonPhone    ($value) { $this->onPropertySet('personPhone',    $value); }
    public function setPersonBadge    ($value) { $this->onPropertySet('personBadge',    $value); }
    public function setPersonGuid     ($value) { $this->onPropertySet('personGuid',     $value); }
    public function setPersonFedId    ($value) { $this->onPropertySet('personFedId',    $value); }
    public function setPersonOrgId    ($value) { $this->onPropertySet('personOrgId',    $value); }
    
    public function setReport         ($value) { $this->onPropertySet('report',         $value); }
    public function setStatus         ($value) { $this->onPropertySet('status',         $value); }
    
    public function setState          ($value) { $this->onPropertySet('state',          $value); }
    public function setStateUpdatedOn ($value) { $this->onPropertySet('stateUpdatedOn', $value); }
    public function setStateUpdatedBy ($value) { $this->onPropertySet('stateUpdatedBy', $value); }
    
    /* =========================================
     * Used to highlite objects
     */
    protected $selected;
    public function getSelected()       { return $this->selected; }
    public function setSelected($value) { $this->selected = $value; return $this; }
}

?>
