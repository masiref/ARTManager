<?php

namespace App\MainBundle\Services;

use App\MainBundle\Entity\Application;
use App\MainBundle\Entity\ExecutionServer;
use App\MainBundle\Entity\TestSet;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Mmoreram\GearmanBundle\Command\Util\GearmanOutputAwareInterface;
use Mmoreram\GearmanBundle\Driver\Gearman\Job;
use Mmoreram\GearmanBundle\Driver\Gearman\Work;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Work(
 *     service="test_set_execution"
 * )
 */
class TestSetExecutionService implements GearmanOutputAwareInterface {

    private $em;
    private $gherkin;
    private $slugify;
    private $locale;
    protected $output;

    public function __construct(EntityManager $em, GherkinService $gherkin, Slugify $slugify) {
        $this->em = $em;
        $this->gherkin = $gherkin;
        $this->slugify = $slugify;
        $this->locale = $gherkin->getLocale();
    }

    /**
     * Run a test set on remote execution server
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @return boolean
     *
     * @Job()
     */
    public function execute(\GearmanJob $job) {
        $em = $this->em;
        $parameters = json_decode($job->workload(), true);
        $testSetId = $parameters["testSetId"];
        $executionServerId = $parameters["executionServerId"];
        $testSetRunId = $parameters["testSetRunId"];
        $testSetRunSlug = $parameters["testSetRunSlug"];

        $testSet = $em->getRepository("AppMainBundle:TestSet")->find($testSetId);
        if ($testSet !== null) {
            $executionServer = $em->getRepository("AppMainBundle:ExecutionServer")->find($executionServerId);
            if ($executionServer !== null) {
                $artRunnerPath = $executionServer->getArtRunnerPath();
                $reportsPath = $artRunnerPath . "reports" . DIRECTORY_SEPARATOR;
                $application = $testSet->getApplication();
                $reportFolderPath = $reportsPath . $testSetRunId . "-" . $testSetRunSlug;

                $this->prepareExecution($testSet, $executionServer, $reportFolderPath);
                $this->launchExecution($application, $executionServer, $reportFolderPath);

                return true;
            }
        }
        return false;
    }

    public function prepareExecution(TestSet $testSet, ExecutionServer $executionServer, $reportFolderPath) {
        $filename = $this->copyFeatureFile($testSet, $executionServer);
        $this->output->writeln($filename . " copied on remote server " . $executionServer->getServer());
        sleep(5);

        $this->createReportFolder($executionServer, $reportFolderPath);
        $this->output->writeln($reportFolderPath . " created on " . $executionServer->getServer());
        sleep(5);
    }

    private function copyFeatureFile(TestSet $testSet, ExecutionServer $executionServer) {
        $server = $executionServer->getServer();
        $gherkin = $this->gherkin;
        $slugify = $this->slugify;
        $artRunnerPath = $executionServer->getArtRunnerPath();
        $featuresPath = $artRunnerPath . "features" . DIRECTORY_SEPARATOR;
        $session = $server->getSession();

        $feature = $testSet->getBehatFeature($gherkin);
        $filename = $featuresPath . $slugify->slugify($testSet->getName()) . ".feature";

        $sftp = $session->getSftp();
        $sftp->write($filename, $feature);

        return $filename;
    }

    private function createReportFolder(ExecutionServer $executionServer, $path) {
        $server = $executionServer->getServer();
        $session = $server->getSession();
        $sftp = $session->getSftp();
        $sftp->mkdir($path);
    }

    private function launchExecution(Application $application, ExecutionServer $executionServer, $reportFolderPath) {
        $server = $executionServer->getServer();
        $artRunnerPath = $executionServer->getArtRunnerPath();
        $session = $server->getSession();
        $exec = $session->getExec();

        $this->output->writeln("behat launched on remote server " . $executionServer->getServer());
        $result = $exec->run(
                "cd " . $artRunnerPath
                . " && ./launcher -u " . $application->getUrl() . " -r " . $reportFolderPath . " -l " . $this->locale
        );
        $this->output->writeln($result);
        return $result;
    }

    public function setOutput(OutputInterface $output) {
        $this->output = $output;
    }

}
