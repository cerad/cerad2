<?php
namespace Cerad\Bundle\PersonBundle\Model;

use Cerad\Bundle\PersonBundle\Model\BaseModel;

use Cerad\Bundle\PersonBundle\Model\Person;

/* =======================================
 * Refactored to make the project key the actual project id
 * 
 * plan.plan
 */
class PersonPlan extends BaseModel
{
    protected $id;
    protected $person;
    protected $projectId;
    protected $status   = 'Active';
    protected $verified = 'No';
    
    // These are basically value objects
    protected $basic = array();
    protected $avail;
    protected $level;
    protected $notes;
   
    public function __construct($id = null, $planProps = array())
    {
    //    $this->id = $id;
    //    $this->setPlanProperties($planProps);
    }
    public function getId()        { return $this->id;        }
    public function getBasic()     { return $this->basic;     }
    public function getNotes()     { return $this->notes;     }
    public function getPerson()    { return $this->person;    }
    public function getStatus()    { return $this->status;    }
    public function getVerified()  { return $this->verified;  }
    public function getProjectId() { return $this->projectId; }
    
    public function setBasic    ($value) { $this->onPropertySet('basic',     $value); }
    public function setNotes    ($value) { $this->onPropertySet('notes',     $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',    $value); }
    public function setVerified ($value) { $this->onPropertySet('verified',  $value); }
    public function setProjectId($value) { $this->onPropertySet('projectId', $value); }
    
    public function setPerson(Person $person) { $this->onPropertySet('person',$person); }
    
    // Initializ from project->basic
    public function mergeBasicProps($props)
    {
        $propx = array();
        foreach($props as $name => $prop)
        {
            $default = array_key_exists('default',$prop) ? $prop['default'] : null;
            $propx[$name] = $default;
        }
        $this->basic = array_merge($propx,$this->basic);
        
    }
    /* ============================================================
     * Need some commanility and consistency
     */
    const WILL_ATTEND  = 'attending';
    const WILL_REFEREE = 'refereeing';
    
    const WILL_MENTOR  = 'willMentor';
    const WANT_MENTOR  = 'wantMentor';
    
    const SHIRT_SIZE  = 'tshirt';
    
    public function getWillAttend () { return $this->basic[self::WILL_ATTEND];  }
    public function getWillReferee() { return $this->basic[self::WILL_REFEREE]; }
    public function getWillMentor () { return $this->basic[self::WILL_MENTOR];  }
    public function getWantMentor () { return $this->basic[self::WANT_MENTOR];  }
    public function getShirtSize  () { return $this->basic[self::SHIRT_SIZE ];  }
    
    public function setWillAttend ($value) { return $this->setBasicParam(self::WILL_ATTEND, $value); }
    public function setWillReferee($value) { return $this->setBasicParam(self::WILL_REFEREE,$value); }
    public function setWillMentor ($value) { return $this->setBasicParam(self::WILL_MENTOR, $value); }
    public function setWantMentor ($value) { return $this->setBasicParam(self::WANT_MENTOR, $value); }
    public function setShirtSize  ($value) { return $this->setBasicParam(self::SHIRT_SIZE,  $value); }
    
    protected function setBasicParam($name,$value)
    {
        if ($value == $this->basic[$name]) return;
        
        // Had problems making this cleaner
        $basic = $this->getBasic();
        
        $basic[$name] = $value;
       
        $this->setBasic($basic);
    }
        
}
?>
