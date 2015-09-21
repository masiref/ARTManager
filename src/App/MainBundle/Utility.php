<?php

namespace App\MainBundle;

use Symfony\Component\Form\Form;

class Utility {

    public static function getErrorsAsString(Form $form) {
        $errors = "";
        $iterator = $form->getErrors(true);
        while ($iterator->current() != null) {
            $errors .= $iterator->current()->getMessage() . "\n";
            $iterator->next();
        }
        return $errors;
    }

    public static function isRegex($string) {
        $regex = "/^\/[\s\S]+\/$/";
        return preg_match($regex, $string);
    }

}
