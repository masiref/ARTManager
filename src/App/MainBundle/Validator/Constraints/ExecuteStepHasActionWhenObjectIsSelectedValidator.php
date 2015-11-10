<?php

namespace App\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExecuteStepHasActionWhenObjectIsSelectedValidator extends ConstraintValidator {

    public function validate($executeStep, Constraint $constraint) {
        if ($executeStep->getObject() !== null && $executeStep->getBusinessStep() === null && $executeStep->getAction() === null) {
            $this->context->addViolation($constraint->message);
        }
    }

}
