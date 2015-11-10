<?php

namespace App\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ExecuteStepHasObjectOrBusinessStep extends Constraint {

    public $message = "Please select an object or a business step (not both).";

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

}
