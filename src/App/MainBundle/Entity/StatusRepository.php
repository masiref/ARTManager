<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StatusRepository extends EntityRepository {

    public function findDefaultTestInstanceStatus() {
        return $this->findOneByName("Not Runned");
    }

}
