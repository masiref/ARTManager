<?php

namespace App\MainBundle\Services;

use Doctrine\ORM\EntityManager;

class TestSetRunService {

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function getTestSetRunsByApplicationTestSet($testSetRuns) {
        $result = array();
        foreach ($testSetRuns as $testSetRun) {
            $testSet = $testSetRun->getTestSet();
            $application = $testSet->getApplication();
            if (!array_key_exists($application->getId(), $result)) {
                $result[$application->getId()] = array(
                    "application" => $application,
                    "testSets" => array()
                );
            }
            if (!array_key_exists($testSet->getId(), $result[$application->getId()]["testSets"])) {
                $result[$application->getId()]["testSets"][$testSet->getId()] = array(
                    "testSet" => $testSet,
                    "testSetRuns" => array()
                );
            }
            $result[$application->getId()]["testSets"][$testSet->getId()]["testSetRuns"][] = $testSetRun;
        }
        return $result;
    }

    public function getPlannedSidebarSection() {
        $em = $this->em;
        $plannedTestSetRuns = $em->getRepository("AppMainBundle:TestSetRun")->findPlannedOrderByCreatedAt();
        return array(
            "count" => count($plannedTestSetRuns),
            "byApplicationTestSet" => $this->getTestSetRunsByApplicationTestSet($plannedTestSetRuns)
        );
    }

    public function getRecentSidebarSection() {
        $em = $this->em;
        $recentTestSetRuns = $em->getRepository("AppMainBundle:TestSetRun")->findRecentOrderByCreatedAt();
        return array(
            "count" => count($recentTestSetRuns),
            "byApplicationTestSet" => $this->getTestSetRunsByApplicationTestSet($recentTestSetRuns)
        );
    }

}
