<?php
namespace Cerad\Library\CommonLibrary\ValidatorConstraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NullOrPositiveIntegerValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) return;
        
        if (is_integer($value) && ($value >= 0)) return;
        
      //if ($value < 0) die('Value ' . $value);
        
        $this->context->addViolation($constraint->message, array('%string%' => $value));
    }

}

?>
