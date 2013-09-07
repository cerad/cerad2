<?php
namespace Cerad\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UsernameExists extends Constraint
{
    public $message = 'User name not found.';
    
    public function validatedBy()
    {
        return 'cerad_user_username_exists';
    }
}

?>
