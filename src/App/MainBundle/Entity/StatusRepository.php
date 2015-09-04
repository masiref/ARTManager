<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StatusRepository extends EntityRepository {

    public function findDefaultTestSetRunStatus() {
        return $this->findOneByName("Queued");
    }

    public function findRunningTestSetRunStatus() {
        return $this->findOneByName("Running");
    }

    public function findFailedTestSetRunStatus() {
        return $this->findOneByName("Failed");
    }

    public function findPassedTestSetRunStatus() {
        return $this->findOneByName("Passed");
    }

}
