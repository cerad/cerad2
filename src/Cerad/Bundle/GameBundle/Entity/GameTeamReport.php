<?php
namespace Cerad\Bundle\GameBundle\Entity;

/* =============================================
 * Value object
 */
class GameTeamReport extends AbstractEntity
{
    protected $team;
    protected $status; // Just because
        
    protected $goalsScored;
    protected $goalsAllowed;
    
    protected $pointsEarned;
    protected $pointsMinus;
    
    protected $sportsmanship; 
    protected $fudgeFactor;
    
    protected $playerWarnings;
    protected $playerEjections;
    
    protected $coachWarnings;
    protected $coachEjections;
    
    protected $benchWarnings;
    protected $benchEjections;
    
    protected $specWarnings;
    protected $specEjections;
 
    static function getPropNames()
    {
        return array(
            'status','goalsScored','goalsAllowed','pointsEarned','pointsMinus',
            'sportsmanship','fudgeFactor',
            'playerWarnings','playerEjections','coachWarnings','coachEjections',
            'benchWarnings','benchEjections','specWarnings','specEjections',
        );
    }
    
    public function getId()     { return $this->id;     }
    public function getTeam()   { return $this->team;   }
    public function getStatus() { return $this->status; }
    
    public function getGoalsScored    ()  { return $this->goalsScored;    }
    public function getGoalsAllowed   ()  { return $this->goalsAllowed;   }
    public function getPointsEarned   ()  { return $this->pointsEarned;   }
    public function getPointsMinus    ()  { return $this->pointsMinus;    }
    
    public function getSportsmanship  ()  { return $this->sportsmanship;  }
    public function getFudgeFactor    ()  { return $this->fudgeFactor;    }
    
    public function getPlayerWarnings ()  { return $this->playerWarnings; }
    public function getPlayerEjections()  { return $this->playerEjections;}
    public function getCoachWarnings  ()  { return $this->coachWarnings;  }
    public function getCoachEjections ()  { return $this->coachEjections; }
    public function getBenchWarnings  ()  { return $this->benchWarnings;  }
    public function getBenchEjections ()  { return $this->benchEjections; }
    public function getSpecWarnings   ()  { return $this->specWarnings;   }
    public function getSpecEjections  ()  { return $this->specEjections;  }
    
    public function setTeam  ($value) { return $this->onPropertySet('team',   $value); }
    public function setStatus($value) { return $this->onPropertySet('status', $value); }
    
    public function setGoalsScored    ($value)  { return $this->onPropertySet('goalsScored',    $value);  }
    public function setGoalsAllowed   ($value)  { return $this->onPropertySet('goalsAllowed',   $value);  }
    public function setPointsEarned   ($value)  { return $this->onPropertySet('pointsEarned',   $value);  }
    public function setPointsMinus    ($value)  { return $this->onPropertySet('pointsMinus',    $value);  }
    
    public function setSportsmanship  ($value)  { return $this->onPropertySet('sportsmanship',  $value);  }
    public function setFudgeFactor    ($value)  { return $this->onPropertySet('fudgeFactor',    $value);  }
    
    public function setPlayerWarnings ($value)  { return $this->onPropertySet('playerWarnings', $value);  }
    public function setPlayerEjections($value)  { return $this->onPropertySet('playerEjections',$value);  }
    public function setCoachWarnings  ($value)  { return $this->onPropertySet('coachWarnings',  $value);  }
    public function setCoachEjections ($value)  { return $this->onPropertySet('coachEjections', $value);  }
    public function setBenchWarnings  ($value)  { return $this->onPropertySet('benchWarnings',  $value);  }
    public function setBenchEjections ($value)  { return $this->onPropertySet('benchEjections', $value);  }
    public function setSpecWarnings   ($value)  { return $this->onPropertySet('specWarnings',   $value);  }
    public function setSpecEjections  ($value)  { return $this->onPropertySet('specEjections',  $value);  }
    
    public function getTotalWarnings()  
    { 
        $total = $this->playerWarnings + $this->coachWarnings + $this->benchWarnings + $this->specWarnings;  
        
        return $total ? $total : null;
        
    }
    public function getTotalEjections()  
    { 
        $total = $this->playerEjections + $this->coachEjections + $this->benchEjections + $this->specEjections;
        
        return $total ? $total : null;
   }
   
    public function __construct($config = null)
    {
        if (!is_array($config)) return;
        
        foreach(self::getPropNames() as $propName)
        {
            if (isset($config[$propName])) $this->$propName = $config[$propName];
        }
    }
    public function clear()
    {
        foreach(self::getPropNames() as $propName)
        {
            $this->$propName = null;
        }
    }
    public function getData()
    {
        $data = array();
        foreach(self::getPropNames() as $propName)
        {
            if (isset($this->$propName)) $data[$propName] = $this->$propName;
        }
        return $data;
    }

    /* ================================================================
     * Maybe mess around with a change listener later
     * Don't expect to be doing mass updates anyways
     */
}
?>
