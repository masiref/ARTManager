<?php

namespace App\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExecuteStepHasObjectOrBusinessStepValidator extends ConstraintValidator {

    public function validate($executeStep, Constraint $constraint) {
        if ($executeStep->getTest() !== null && $executeStep->getObject() !== null && $executeStep->getBusinessStep() !== null) {
            $this->context->addViolation($constraint->message);
        }
    }

}
