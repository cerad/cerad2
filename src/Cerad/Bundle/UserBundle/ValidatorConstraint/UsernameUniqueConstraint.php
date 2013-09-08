<?php
namespace Cerad\Bundle\UserBundle\ValidatorConstraint;

use Symfony\Component\Validator\Constraint;

class UsernameUniqueConstraint extends Constraint
{
    public $message = 'User name already in use.';
    
    public function validatedBy()
    {
        return 'cerad_user_username_unique';
    }
}

?>
