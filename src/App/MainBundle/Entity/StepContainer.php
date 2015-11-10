<?php

namespace App\MainBundle\Entity;

abstract class StepContainer {

    public abstract function getFolder();

    public abstract function setFolder($folder);
}
