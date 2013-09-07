<?php
namespace Cerad\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class EmailExists extends Constraint
{
    public $message = 'Email not found.';
    
    public function validatedBy()
    {
        return 'cerad_user_email_exists';
    }
}

?>
