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
    
    public function getWillAttend () { return $this->basic[self::WILL_ATTEND];  }
    public function getWillReferee() { return $this->basic[self::WILL_REFEREE]; }
    
    public function setWillAttend($value)
    {
        if ($value == $this->basic[self::WILL_ATTEND]) return;
        
        $basic = $this->getBasic();
        
        $basic[self::WILL_ATTEND] = $value;
       
        $this->setBasic($basic);
    }
    public function setWillReferee($value)
    {
        if ($value == $this->basic[self::WILL_REFEREE]) return;
        
        $basic = $this->getBasic();
        
        $basic[self::WILL_REFEREE] = $value;
        
        $this->setBasic($basic);
        
        return;
        
        /* ======================================================
         * For some reason, this ends up clearing the basic array
         */
        if ($value == $this->basic[self::WILL_REFEREE]) return;
        
        $this->basic[self::WILL_REFEREE] = $value;
           
        $this->onPropertyChanged('basic');     
    }
}
?>
