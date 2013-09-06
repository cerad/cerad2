<?php

namespace Cerad\Bundle\PersonBundle\Model;

class Person extends BaseModel
{
    const GenderMale    = 'M';
    const GenderFemale  = 'F';
    const GenderUnknown = 'U';

    protected $id;
    protected $name;   // VO PersonName
        
    protected $dob;    // DateTime
    protected $gender;
    
    protected $email;

    protected $phone;
    protected $phoneProvider; // For texting?

    protected $address; // VO PersonAddress
    
    protected $notes;   // Not sure,
        
    protected $verified  = 'No';
    protected $status    = 'Active';
    
    protected $feds    = array();
    protected $plans   = array();
    protected $persons = array();
    
    public function __construct()
    {
        $this->id = $this->genId(); // Does the model really need these?
        
        $this->name    = $this->createName();
        $this->address = $this->createAddress();
    }
    /* ======================================================================
     * Standard getter/setters
     */
    public function getId       () { return $this->id;     }
    public function getDob      () { return $this->dob;    }
    public function getNotes    () { return $this->notes;  }
    public function getEmail    () { return $this->email;  }
    public function getPhone    () { return $this->phone;  }
    public function getGender   () { return $this->gender; }

    public function getStatus   () { return $this->status;    }
    public function getVerified () { return $this->verified;  }

    // Value Objects
    public function getName     () { return clone $this->name;    }
    public function getAddress  () { return clone $this->address; }
    
    public function createName   ($params = null) { return new PersonName   ($params); }
    public function createAddress($params = null) { return new PersonAddress($params); }
    
    public function setId       ($value) { $this->onPropertySet('id',       $value); }
    public function setDob      ($value) { $this->onPropertySet('dob',      $value); }
    public function setName     ($value) { $this->onPropertySet('name',     $value); }
    public function setNotes    ($value) { $this->onPropertySet('notes',    $value); }
    public function setEmail    ($value) { $this->onPropertySet('email',    $value); }
    public function setPhone    ($value) { $this->onPropertySet('phone',    $value); }
    public function setGender   ($value) { $this->onPropertySet('gender',   $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }
    public function setAddress  ($value) { $this->onPropertySet('address',  $value); }
    public function setVerified ($value) { $this->onPropertySet('verified', $value); }

    static function getGenderTypes()
    {
        return array(
            self::GenderMale    => 'Male',
            self::GenderFemale  => 'Female',
            self::GenderUnknown => 'Unknown',
        );
    }
    /* =============================================================
     * The feds stuff
     */
    public function createFed($params = null) { return new PersonFed($params); }
    
    public function getFeds() { return  $this->feds; }
    
    public function addFed(PersonFed $item)
    {
        
    }
    public function findFed($id, $autoCreate = true, $autoAdd = true)
    {
        
    }
}
?>
