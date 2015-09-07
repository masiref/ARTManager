<?php

namespace App\MainBundle\Entity;

use App\MainBundle\Services\GherkinService;
use App\MainBundle\Services\MinkService;

class BehatScenario {

    private $test;
    private $gherkin;
    private $mink;
    private $locale;

    public function __construct(Test $test, GherkinService $gherkin, MinkService $mink) {
        $this->test = $test;
        $this->gherkin = $gherkin;
        $this->mink = $mink;
        $this->locale = $gherkin->getLocale();
    }

    public function generate() {
        $gherkin = $this->gherkin;
        $mink = $this->mink;

        // scenario node
        $content = $gherkin->getScenarioKeyword() . ": " . $this->test->getName() . PHP_EOL;

        $prefix = "  ";

        if ($this->test->getStartingPage() != null) {
            // prerequisites
            $prerequisites = $this->test->getPrerequisites();
            if ($prerequisites->count() > 0) {
                $content .= $prefix . "# ==== prerequisites ================" . PHP_EOL;
            }
            foreach ($prerequisites as $key => $prerequisite) {
                $test = $prerequisite->getTest();
                $content .= $prefix . "# " . $test . PHP_EOL;
                if ($key == 0) {
                    $content .= $prefix . $gherkin->getGivenKeyword() . " " . $mink->getIAmOnPageStep($test->getStartingPage()) . PHP_EOL;
                }
                $scenario = new BehatScenario($test, $gherkin, $mink);
                $steps = $scenario->generateSteps();
                $content .= $steps;
            }

            // initial state node
            if ($prerequisites->count() == 0) {
                $content .= $prefix . $gherkin->getGivenKeyword() . " " . $mink->getIAmOnPageStep($this->test->getStartingPage()) . PHP_EOL;
            } else {
                $content .= $prefix . "# ==== end prerequisites ============" . PHP_EOL;
            }

            // steps nodes
            return $content . $this->generateSteps();
        }
        return $content;
    }

    private function generateSteps() {
        $gherkin = $this->gherkin;
        $locale = $this->locale;

        $prefix = "  ";

        $content = "";
        $previousStepHasControlSteps = false;
        foreach ($this->test->getSteps() as $i => $step) {
            $stepPrefix = ($previousStepHasControlSteps || $i == 0 ? $gherkin->getWhenKeyword() : $gherkin->getAndKeyword()) . " ";
            $content .= $prefix . $stepPrefix . $step->getMinkSentence($locale) . PHP_EOL;
            $controlSteps = $step->getControlSteps();
            $previousStepHasControlSteps = $controlSteps->count() > 0;
            foreach ($controlSteps as $j => $controlStep) {
                $controlStepPrefix = ($j > 0 ? $gherkin->getAndKeyword() : $gherkin->getThenKeyword()) . " ";
                $content .= $prefix . $controlStepPrefix . $controlStep->getMinkSentence($locale) . PHP_EOL;
            }
        }
        return $content;
    }

}
