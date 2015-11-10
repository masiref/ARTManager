<?php

namespace App\MainBundle\Entity;

use App\MainBundle\Services\GherkinService;
use App\MainBundle\Services\MinkService;

class BehatScenario {

    private $test;
    private $gherkin;
    private $mink;
    private $locale;

    public function __construct($test, GherkinService $gherkin, MinkService $mink) {
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

    private function generateSteps($fromStep = null) {
        $gherkin = $this->gherkin;
        $mink = $this->mink;
        $locale = $this->locale;

        $prefix = "  ";

        $content = "";
        $previousStepHasControlSteps = false;
        foreach ($this->test->getSteps() as $i => $step) {
            $stepPrefix = ($previousStepHasControlSteps || $i == 0 ? $gherkin->getWhenKeyword() : $gherkin->getAndKeyword()) . " ";
            $businessStep = ($this->test instanceof Test) ? $step->getBusinessStep() : null;
            $previousBusinessStepHasControlSteps = false;
            if ($businessStep !== null) {
                $content .= $prefix . "# ==== business step ================" . PHP_EOL;
                $content .= $prefix . "# " . $businessStep . PHP_EOL;
                $scenario = new BehatScenario($businessStep, $gherkin, $mink);
                $steps = $scenario->generateSteps($step);
                $content .= $steps;
                $content .= $prefix . "# ==== end business step ============" . PHP_EOL;
                $previousBusinessStepHasControlSteps = $businessStep->isLastStepHasControlSteps();
            } else {
                $sentence = $step->getMinkSentence($locale);
                if ($fromStep !== null) {
                    $sentence = $this->updateMinkSentence($sentence, $fromStep);
                }
                $content .= $prefix . $stepPrefix . $sentence . PHP_EOL;
            }
            $controlSteps = $step->getControlSteps();
            foreach ($controlSteps as $j => $controlStep) {
                $controlStepPrefix = ($j > 0 || $previousBusinessStepHasControlSteps ? $gherkin->getAndKeyword() : $gherkin->getThenKeyword()) . " ";
                $sentence = $controlStep->getMinkSentence($locale);
                if ($fromStep !== null) {
                    $sentence = $this->updateMinkSentence($sentence, $fromStep);
                }
                $content .= $prefix . $controlStepPrefix . $sentence . PHP_EOL;
            }
            $previousStepHasControlSteps = $controlSteps->count() > 0;
        }
        return $content;
    }

    private function updateMinkSentence($sentence, $step) {
        foreach ($step->getParameterDatas() as $parameterData) {
            $sentence = str_replace("%" . $parameterData->getParameter()->getPlaceholder() . "%", $parameterData->getValue(), $sentence);
        }
        return $sentence;
    }

}
