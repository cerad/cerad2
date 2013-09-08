<?php
namespace Cerad\Bundle\UserBundle\ValidatorConstraint;

use Symfony\Component\Validator\Constraint;

class EmailExistsConstraint extends Constraint
{
    public $message = 'Email not found.';
    
    public function validatedBy()
    {
        return 'cerad_user_email_exists';
    }
}

?>
