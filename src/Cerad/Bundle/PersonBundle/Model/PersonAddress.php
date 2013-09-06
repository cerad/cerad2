<?php
namespace Cerad\Bundle\PersonBundle\Model;

class PersonAddress extends BaseValueObject
{
    public $street1;
    public $street2;
    public $city;
    public $state;
    public $country;
    public $zipcode;
   
    public function __construct(
        $street1 = null,
        $street2 = null,
        $city    = null,
        $state   = null,
        $country = null,
        $zipcode = null
    )
    {
        $this->street1 = $street1;
        $this->street2 = $street2;
        $this->city    = $city;
        $this->state   = $state;
        $this->country = $country;
        $this->zipcode = $zipcode;
    }
}
?>
