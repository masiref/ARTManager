<?php

namespace App\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ControlStepHasPageOrObjectValidator extends ConstraintValidator {

    public function validate($controlStep, Constraint $constraint) {
        if ($controlStep->getPage() !== null && $controlStep->getObject() !== null) {
            $this->context->addViolation($constraint->message);
        }
    }

}
