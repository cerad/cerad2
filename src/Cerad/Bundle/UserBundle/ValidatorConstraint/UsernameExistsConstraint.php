<?php
namespace Cerad\Bundle\UserBundle\ValidatorConstraint;

use Symfony\Component\Validator\Constraint;

class UsernameExistsConstraint extends Constraint
{
    public $message = 'User name not found.';
    
    public function validatedBy()
    {
        return 'cerad_user_username_exists';
    }
}

?>
