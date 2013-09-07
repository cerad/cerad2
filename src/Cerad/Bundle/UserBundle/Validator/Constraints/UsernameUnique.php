<?php
namespace Cerad\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UsernameUnique extends Constraint
{
    public $message = 'User name already in use.';
    
    public function validatedBy()
    {
        return 'cerad_user_username_unique';
    }
}

?>
