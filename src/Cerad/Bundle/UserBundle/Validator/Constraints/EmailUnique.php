<?php
namespace Cerad\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class EmailUnique extends Constraint
{
    public $message = 'Email already in use.';
    
    public function validatedBy()
    {
        return 'cerad_user_email_unique';
    }
}

?>
