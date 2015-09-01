<?php

namespace App\MainBundle\Workers;

use Mmoreram\GearmanBundle\Driver\Gearman\Job;
use Mmoreram\GearmanBundle\Driver\Gearman\Work;

/**
 * @Work(
 *     iterations = 1,
 *     description = "Test set execution worker"
 * )
 */
class TestSetExecutionWorker {

    /**
     * Test method to run as a job
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @return boolean
     *
     * @Job()
     */
    public function testA(\GearmanJob $job) {
        echo "Job testA done!" . PHP_EOL;
        sleep(10);
        return true;
    }

}
