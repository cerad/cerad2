<?php
namespace Cerad\Bundle\UserBundle\ValidatorConstraint;

use Symfony\Component\Validator\Constraint;

class UsernameOrEmailExistsConstraint extends Constraint
{
    public $message = 'Neither user name nor email were found.';
    
    public function validatedBy()
    {
        return 'cerad_user_username_or_email_exists';
    }
}

?>
