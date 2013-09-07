<?php
namespace Cerad\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UsernameOrEmailExists extends Constraint
{
    public $message = 'Neither user name nor email were found.';
    
    public function validatedBy()
    {
        return 'cerad_user_username_or_email_exists';
    }
}

?>
