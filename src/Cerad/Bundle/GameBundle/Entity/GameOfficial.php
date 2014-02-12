<?php
namespace Cerad\Bundle\GameBundle\Entity;

/* ========================================================
 * Need a person value object
UPDATE game_officials SET assign_role = 'USER_ROLE' 
WHERE  game_officials.game_id IN 
(SELECT id FROM games WHERE DATE(games.dt_beg) IN ('2014-02-22','2014-03-01'))

 */
class GameOfficial extends AbstractEntity
{
    protected $id;
    
    protected $game;
    protected $slot; // 1-5 for arbiter
    protected $role; // Referee, AR1 etc
    
    protected $assignRole;            // ROLE_USER, ROLE_ASSIGNOR
    protected $assignState = 'Open';  // Current state, see AssignSlotWorkflow
    
    protected $personNameFull;
    protected $personNameLast;
    protected $personNameFirst;
    protected $personEmail;
    protected $personPhone;
    protected $personBadge;
    protected $personGuid;
    protected $personFedId; // AKA FedKey
    protected $personOrgId; // AKA OrgKey
    
    protected $report;
    protected $status = 'Active';
   
    // TODO: Rename to assignStateUpdated
    protected $stateUpdatedOn;
    protected $stateUpdatedBy;
    
    
    public function getId  () { return $this->id;     }
    public function getGame() { return $this->game;   }
    public function getSlot() { return $this->slot;   }
    public function getRole() { return $this->role;   }
    
    public function getAssignRole     () { return $this->assignRole;      }
    public function getAssignState    () { return $this->assignState;     }
    
    public function getPersonNameFull () { return $this->personNameFull;  }
    public function getPersonNameLast () { return $this->personNameLast;  }
    public function getPersonNameFirst() { return $this->personNameFirst; }
    public function getPersonEmail    () { return $this->personEmail;     }
    public function getPersonPhone    () { return $this->personPhone;     }
    public function getPersonBadge    () { return $this->personBadge;     }
    public function getPersonGuid     () { return $this->personGuid;      }
    public function getPersonFedId    () { return $this->personFedId;     }
    public function getPersonOrgId    () { return $this->personOrgId;     }
    public function getPersonFedKey   () { return $this->personFedId;     }
    public function getPersonOrgKey   () { return $this->personOrgId;     }
    
    public function getReport()          { return $this->report;          }
    public function getStatus()          { return $this->status;          }
    
    public function getState         () { return $this->assignState;    }
    public function getStateUpdatedOn() { return $this->stateUpdatedOn; }
    public function getStateUpdatedBy() { return $this->stateUpdatedBy; }

    public function setGame($value) { $this->onPropertySet('game',  $value); }
    public function setSlot($value) { $this->onPropertySet('slot',  $value); } 
    public function setRole($value) { $this->onPropertySet('role',  $value); }
    
    public function setAssignRole     ($value) { $this->onPropertySet('assignRole', $value); }
    public function setAssignState    ($value) { $this->onPropertySet('assignState',$value); }
     
    public function setPersonNameFull ($value) { $this->onPropertySet('personNameFull', $value); }
    public function setPersonNameLast ($value) { $this->onPropertySet('personNameLast', $value); }
    public function setPersonNameFirst($value) { $this->onPropertySet('personNameFirst',$value); }
    public function setPersonEmail    ($value) { $this->onPropertySet('personEmail',    $value); }
    public function setPersonPhone    ($value) { $this->onPropertySet('personPhone',    $value); }
    public function setPersonBadge    ($value) { $this->onPropertySet('personBadge',    $value); }
    public function setPersonGuid     ($value) { $this->onPropertySet('personGuid',     $value); }
    public function setPersonFedId    ($value) { $this->onPropertySet('personFedId',    $value); }
    public function setPersonOrgId    ($value) { $this->onPropertySet('personOrgId',    $value); }
    public function setPersonFedKey   ($value) { $this->onPropertySet('personFedId',    $value); }
    public function setPersonOrgKey   ($value) { $this->onPropertySet('personOrgId',    $value); }
    
    public function setReport         ($value) { $this->onPropertySet('report',         $value); }
    public function setStatus         ($value) { $this->onPropertySet('status',         $value); }
    
    public function setStateUpdatedOn ($value) { $this->onPropertySet('stateUpdatedOn', $value); }
    public function setStateUpdatedBy ($value) { $this->onPropertySet('stateUpdatedBy', $value); }
    
    /* ===================================================
     * Are users allowed to self assign?
     */
    public function isAssignableByUser()
    {
        return $this->assignRole == 'ROLE_USER' ? true : false;
    }
    /* ==================================================
     * The isUpdatable is used to indicate the current user actually
     * has permission to update this entity
     * 
     * TODO: This stuff should really be in an GameOfficialActionModel wrapper
     *       Part of the yet to be defined ViewModel
     * 
     * Tried just making them public but Doctrine 2 complains.
     */
    protected $isSelected      = false;
    protected $isUserUpdatable = false;
    
    protected $originalInfo = array();
    
    public function saveOriginalInfo()
    {
        $this->originalInfo = array
        (
            'assignState'    => $this->assignState,
            'personGuid'     => $this->personGuid,
            'personNameFull' => $this->personNameFull,
        );
    }
    public function restoreOriginalInfo()
    {
        $this->assignState    = $this->originalInfo['assignState'];
        $this->personGuid     = $this->originalInfo['assignGuid'];
        $this->personNameFull = $this->originalInfo['personNameFull'];
    }
    public function retrieveOriginalInfo()
    {
        return $this->originalInfo;
    }
    public function __get($name)
    {
        switch($name)
        {
            case 'isSelected':
            case 'isUserUpdateable':
                return $this->$name;
        }
    }
    public function __set($name,$value)
    {
        switch($name)
        {
            case 'isSelected':
            case 'isUserUpdateable':
                $this->$name = $value;
                return $this;
        }
    }
    // Copies or clears person info
    public function setPersonFromPlan($personPlan)
    {
        if (!$personPlan)
        {
            $this->setPersonGuid    (null);
            $this->setPersonNameFull(null);
            return;
        }
        $person = $personPlan->getPerson();
        $this->setPersonGuid    ($person->getGuid());
        $this->setPersonNameFull($personPlan->getPersonName());
    }
}

?>
