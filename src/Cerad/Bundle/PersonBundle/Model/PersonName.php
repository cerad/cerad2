<?php
namespace Cerad\Bundle\PersonBundle\Model;

/* ==========================================
 * First shot at a value object
 * It's mutable so forms can use it
 * 
 * Seems to work okay, need some doctrine event listeners
 */
class PersonName extends BaseValueObject
{
    public $fullName;
    public $firstName;
    public $lastName;
    public $nickName;
    public $middleName;
    
    public function __construct(
        $fullName   = null, 
        $firstName  = null, 
        $lastName   = null, 
        $nickName   = null, 
        $middleName = null)
    {
        $this->fullName   = $fullName;
        $this->firstName  = $firstName;
        $this->lastName   = $lastName;
        $this->nickName   = $nickName;
        $this->middleName = $middleName;
    }
}
?>
