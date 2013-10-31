<?php
namespace Cerad\Library\CommonLibrary\ValidatorConstraint;

use Symfony\Component\Validator\Constraint;

class NullOrPositiveIntegerConstraint extends Constraint
{
    public $message = 'Cannot be negative';
    
    public function validatedBy()
    {
        return 'Cerad\Library\CommonLibrary\ValidatorConstraint\NullOrPositiveIntegerValidator';
    }
}

?>
