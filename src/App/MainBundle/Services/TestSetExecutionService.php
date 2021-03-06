<?php

namespace App\MainBundle\Services;

use App\MainBundle\Entity\Application;
use App\MainBundle\Entity\ExecutionReport;
use App\MainBundle\Entity\ExecutionServer;
use App\MainBundle\Entity\Status;
use App\MainBundle\Entity\TestSet;
use App\MainBundle\Entity\TestSetRun;
use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
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
        $this->output->writeln("test set run id => " . $testSetRunId);
        $testSetRun = $em->getRepository("AppMainBundle:TestSetRun")->find($testSetRunId);
        if ($testSet !== null) {
            $executionServer = $em->getRepository("AppMainBundle:ExecutionServer")->find($executionServerId);
            if ($executionServer !== null) {
                $artRunnerPath = $executionServer->getArtRunnerPath();
                $reportsPath = $artRunnerPath . "reports" . DIRECTORY_SEPARATOR;
                $application = $testSet->getApplication();
                $reportFolderPath = $reportsPath . $testSetRunId . "-" . $testSetRunSlug;

                $this->output->writeln(">>> updating execution status (running)");
                $testSetRun->setStartedAt(new DateTime());
                $this->updateTestSetRunStatus($testSetRun, $em->getRepository("AppMainBundle:Status")->findRunningTestSetRunStatus());

                $server = $executionServer->getServer();
                $passed = false;

                if ($server->checkConnection()) {
                    $this->output->writeln(">>> preparing execution");
                    $this->createReportFolder($executionServer, $reportFolderPath);
                    $this->output->writeln($reportFolderPath . " created on " . $server);

                    $featureFilePath = $this->copyFeatureFile($testSet, $executionServer);
                    $this->output->writeln($featureFilePath . " copied on " . $server);

                    $this->output->writeln(">>> launching execution");
                    $passed = $this->launchExecution($application, $executionServer, $reportFolderPath);

                    $this->output->writeln(">>> storing execution report");
                    $this->storeExecutionReport($testSetRun, $executionServer, $reportFolderPath);

                    $this->output->writeln(">>> cleaning execution");
                    $this->cleanExecution($executionServer, $featureFilePath);
                }

                $testSetRun->setEndedAt(new DateTime());
                $this->output->writeln(">>> updating execution status (passed or failed)");
                $this->updateFinishedTestSetRunStatus($testSetRun, $passed);

                return true;
            }
        }
        return false;
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
        $this->output->writeln("cd " . $artRunnerPath
                . " && ./launcher -u " . $application->getUrl() . " -r " . $reportFolderPath . " -l " . $this->locale);
        try {
            $output = $exec->run(
                    "cd " . $artRunnerPath
                    . " && ./launcher -u " . $application->getUrl() . " -r " . $reportFolderPath . " -l " . $this->locale
            );
        } catch (Exception $e) {
            $e->getMessage();
            return false;
        }
        $this->output->writeln("return code => " . $output);
        return true;
    }

    private function cleanExecution(ExecutionServer $executionServer, $featureFilePath) {
        $server = $executionServer->getServer();
        $session = $server->getSession();
        $sftp = $session->getSftp();
        $sftp->unlink($featureFilePath);
    }

    private function updateFinishedTestSetRunStatus(TestSetRun $testSetRun, $passed) {
        $em = $this->em;
        if ($passed) {
            $status = $em->getRepository("AppMainBundle:Status")->findPassedTestSetRunStatus();
        } else {
            $status = $em->getRepository("AppMainBundle:Status")->findFailedTestSetRunStatus();
        }
        $this->updateTestSetRunStatus($testSetRun, $status);
    }

    private function updateTestSetRunStatus(TestSetRun $testSetRun, Status $status) {
        $em = $this->em;
        $testSetRun->setStatus($status);
        $em->persist($testSetRun);
        $em->flush();
    }

    private function storeExecutionReport(TestSetRun $testSetRun, ExecutionServer $executionServer, $reportFolderPath) {
        $em = $this->em;
        $server = $executionServer->getServer();
        $session = $server->getSession();
        $sftp = $session->getSftp();
        $this->output->writeln("reading content of file : " . $reportFolderPath . DIRECTORY_SEPARATOR . "report.out");
        $reportFile = $sftp->read($reportFolderPath . DIRECTORY_SEPARATOR . "report.out");
        $executionReport = new ExecutionReport();
        $executionReport->setReport($reportFile);
        $testSetRun->setExecutionReport($executionReport);
        $em->persist($testSetRun);
        $em->flush();
    }

    public function setOutput(OutputInterface $output) {
        $this->output = $output;
    }

}
