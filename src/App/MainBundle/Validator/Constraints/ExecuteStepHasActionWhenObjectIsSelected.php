<?php

namespace App\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ExecuteStepHasActionWhenObjectIsSelected extends Constraint {

    public $message = "Please select an action.";

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

}
