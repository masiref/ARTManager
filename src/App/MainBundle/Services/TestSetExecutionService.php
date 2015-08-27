<?php

namespace App\MainBundle\Services;

use App\MainBundle\Entity\ExecutionServer;
use App\MainBundle\Entity\TestSet;
use Cocur\Slugify\Slugify;

class TestSetExecutionService {

    private $gherkin;
    private $slugify;

    public function __construct(GherkinService $gherkin, Slugify $slugify) {
        $this->gherkin = $gherkin;
        $this->slugify = $slugify;
    }

    public function copyFeatureFileOnExecutionServer(TestSet $testSet, ExecutionServer $executionServer) {
        $server = $executionServer->getServer();
        $gherkin = $this->gherkin;
        $slugify = $this->slugify;

        $feature = $testSet->getBehatFeature($gherkin);
        $filename = $executionServer->getArtRunnerPath() . "features" . DIRECTORY_SEPARATOR . $slugify->slugify($testSet->getName()) . ".feature";

        $session = $server->getSession();
        $sftp = $session->getSftp();
        $sftp->write($filename, $feature);

        return $filename;
    }

}
