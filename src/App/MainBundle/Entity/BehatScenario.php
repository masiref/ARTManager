<?php

namespace App\MainBundle\Entity;

use App\MainBundle\Services\GherkinService;
use App\MainBundle\Services\MinkService;

class BehatScenario {

    private $test;

    public function __construct(Test $test) {
        $this->test = $test;
    }

    public function generate(GherkinService $gherkin, MinkService $mink) {
        $locale = $gherkin->getLocale();

        // scenario node
        $content = $gherkin->getScenarioKeyword() . ": " . $this->test->getName() . PHP_EOL;

        $prefix = "  ";

        // initial state node
        $content .= $prefix . $gherkin->getGivenKeyword() . " " . $mink->getIAmOnPageStep($this->test->getStartingPage()) . PHP_EOL;

        // steps nodes
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
