<?php

namespace App\MainBundle\Entity;

use App\MainBundle\Services\GherkinService;
use App\MainBundle\Services\MinkService;

class BehatFeature {

    private $testSet;
    private $gherkin;
    private $mink;
    private $locale;

    public function __construct(TestSet $testSet, GherkinService $gherkin, MinkService $mink) {
        $this->testSet = $testSet;
        $this->gherkin = $gherkin;
        $this->mink = $mink;
        $this->locale = $gherkin->getLocale();
    }

    public function generate() {
        $gherkin = $this->gherkin;
        $mink = $this->mink;
        $testSet = $this->testSet;

        // feature node
        $content = $gherkin->getFeatureKeyword() . ": " . $testSet->getName() . PHP_EOL;
        $description = $testSet->getDescription();
        if ($description != null && $description != "") {
            $content .= $description . PHP_EOL;
        }
        $content .= PHP_EOL;

        // scenario nodes
        foreach ($testSet->getTestInstances() as $testInstance) {
            $scenario = new BehatScenario($testInstance->getTest(), $gherkin, $mink);
            $content .= $scenario->generate();
            $content .= PHP_EOL;
        }

        return $content;
    }

}
