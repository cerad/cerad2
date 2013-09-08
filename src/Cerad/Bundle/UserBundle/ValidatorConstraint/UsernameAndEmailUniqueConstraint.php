<?php
namespace Cerad\Bundle\UserBundle\ValidatorConstraint;

use Symfony\Component\Validator\Constraint;

class UsernameAndEmailUniqueConstraint extends Constraint
{
    public $message = 'User name or email already in use.';
    
    public function validatedBy()
    {
        return 'cerad_user_username_and_email_unique';
    }
}

?>
