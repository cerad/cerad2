<?php
namespace Cerad\Bundle\UserBundle\ValidatorConstraint;

use Symfony\Component\Validator\Constraint;

class EmailUniqueConstraint extends Constraint
{
    public $message = 'Email already in use.';
    
    public function validatedBy()
    {
        return 'cerad_user_email_unique';
    }
}

?>
