<?php

namespace Cerad\Bundle\PersonBundle\Model;

class Person extends BaseModel
{
    const GenderMale    = 'M';
    const GenderFemale  = 'F';
    const GenderUnknown = 'U';

    protected $id;
    protected $name; // VO PersonName
    protected $note;
        
    protected $gender;
    
    protected $dob; // DateTime
    
    protected $email;

    protected $phone;
    protected $phoneProvider; // For texting?

    protected $address; // VO PersonAddress
        
    protected $verified  = 'No';
    protected $status    = 'Active';
    
    protected $fedOffs = array();
    protected $plans;
    protected $persons;
    
    public function __construct()
    {
        $this->id = $this->genId(); // Does the model really need these?
        
        $this->name    = new PersonName();
        $this->address = new PersonAddress();
    }
    /* ======================================================================
     * Standard getter/setters
     */
    public function getId       () { return $this->id;     }
    public function getDob      () { return $this->dob;    }
    public function getNote     () { return $this->note;   }
    public function getEmail    () { return $this->email;  }
    public function getPhone    () { return $this->phone;  }

    public function getStatus   () { return $this->status;    }
    public function getGender   () { return $this->gender;    }
    public function getVerified () { return $this->verified;  }

    // Value Objects
    public function getName     () { return clone $this->name;    }
    public function getAddress  () { return clone $this->address; }
    
    public function setId       ($value) { $this->onPropertySet('id',       $value); }
    public function setDob      ($value) { $this->onPropertySet('dob',      $value); }
    public function setName     ($value) { $this->onPropertySet('name',     $value); }
    public function setNote     ($value) { $this->onPropertySet('note',     $value); }
    public function setEmail    ($value) { $this->onPropertySet('email',    $value); }
    public function setPhone    ($value) { $this->onPropertySet('phone',    $value); }
    public function setGender   ($value) { $this->onPropertySet('gender',   $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }
    public function setAddress  ($value) { $this->onPropertySet('address',  $value); }
    public function setVerified ($value) { $this->onPropertySet('verified', $value); }

    /* =============================================================
     * The feds stuff
     */
    public function createFedOff() { return new PersonFedOff(); }
    
    public function getFedOffs() { return  $this->fedOffs; }
    
}
?>
