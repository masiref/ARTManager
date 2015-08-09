<?php

namespace App\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ControlStepHasPageOrObject extends Constraint {

    public $message = "Please select a page or an object (not both).";

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

}
