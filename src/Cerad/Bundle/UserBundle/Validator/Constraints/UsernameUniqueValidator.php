<?php
namespace Cerad\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Cerad\Bundle\UserBundle\Model\UserManagerInterface as UserModelManagerInterface;

class UsernameUniqueValidator extends ConstraintValidator
{
    protected $manager;
    
    public function __construct(UserModelManagerInterface $manager)
    {
        $this->manager = $manager;
    }    
    public function validate($value, Constraint $constraint)
    {
        // Takes care of all the can nonsense
        if (!$this->manager->findUserByUsername($value)) return;
       
        $this->context->addViolation($constraint->message, array('%string%' => $value));
    }
}

?>
