<?php

namespace Cerad\Bundle\PersonBundle\Model;

class Person extends BaseModel
{
    const GenderMale    = 'M';
    const GenderFemale  = 'F';
    const GenderUnknown = 'U';

    protected $id;
    protected $name;
    protected $note;
    
    protected $lastName;
    protected $nickName;  // Obsolete?
    
    protected $firstName;
    
    protected $gender;
    
    protected $dob; // DateTime
    
    protected $email;

    protected $phone;
    protected $phoneProvider; // For texting?

    protected $city;
    protected $state;
    protected $zipcode;
    
    protected $verified  = 'No';
    protected $status    = 'Active';
    
    protected $fedOffs;
    protected $plans;
    protected $persons;
    
    public function __construct()
    {
        $this->id = $this->genId(); // Does the model really need these?
        
        $this->fedOffs = array();
    }
    
    /* ======================================================================
     * Standard getter/setters
     */
    public function getId       () { return $this->id;     }
    public function getDob      () { return $this->dob;    }
    public function getName     () { return $this->name;   }
    public function getNote     () { return $this->note;   }
    public function getEmail    () { return $this->email;  }
    public function getPhone    () { return $this->phone;  }
    
    public function getCity     () { return $this->city;   }
    public function getState    () { return $this->state;  }

    public function getStatus   () { return $this->status;    }
    public function getGender   () { return $this->gender;    }
    public function getVerified () { return $this->verified;  }
    public function getLastName () { return $this->lastName;  }
    public function getNickName () { return $this->nickName;  }
    public function getFirstName() { return $this->firstName; }

    public function setId       ($value) { $this->onPropertySet('id',       $value); }
    public function setDob      ($value) { $this->onPropertySet('dob',      $value); }
    public function setName     ($value) { $this->onPropertySet('name',     $value); }
    public function setNote     ($value) { $this->onPropertySet('note',     $value); }
    public function setCity     ($value) { $this->onPropertySet('city',     $value); }
    public function setState    ($value) { $this->onPropertySet('state',    $value); }
    public function setEmail    ($value) { $this->onPropertySet('email',    $value); }
    public function setPhone    ($value) { $this->onPropertySet('phone',    $value); }
    public function setGender   ($value) { $this->onPropertySet('gender',   $value); }
    public function setStatus   ($value) { $this->onPropertySet('status',   $value); }
    public function setVerified ($value) { $this->onPropertySet('verified', $value); }
    public function setLastName ($value) { $this->onPropertySet('lastName', $value); }
    public function setNickName ($value) { $this->onPropertySet('nickName', $value); }
    public function setFirstName($value) { $this->onPropertySet('firstName',$value); }

    /* =============================================================
     * The feds stuff
     */
    public function createFedOff() { return new PersonFedOff(); }
    
    public function getFedOffs() { return  $this->fedOffs; }
}
?>
