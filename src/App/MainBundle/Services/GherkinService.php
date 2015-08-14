<?php

namespace App\MainBundle\Services;

use App\MainBundle\Entity\BehatScenario;
use App\MainBundle\Entity\Test;
use Behat\Gherkin\Keywords\CucumberKeywords;
use Symfony\Component\DependencyInjection\Container;

class GherkinService {

    private $locale;
    private $keywords;
    private $mink;

    public function __construct(Container $container) {
        $this->locale = $container->getParameter("locale");
        $yaml = $container->get('kernel')->locateResource("@AppMainBundle/Resources/config/cucumber/gherkin/i18n.yml");
        $this->keywords = new CucumberKeywords($yaml);
        $this->keywords->setLanguage($this->locale);
        $this->mink = $container->get('mink');
    }

    public function generateBehatScenario(Test $test) {
        $scenario = new BehatScenario($test);
        return $scenario->generate($this, $this->mink);
    }

    public function getScenarioKeyword() {
        return explode("|", $this->keywords->getScenarioKeywords())[0];
    }

    public function getGivenKeyword() {
        return explode("|", $this->keywords->getGivenKeywords())[0];
    }

    public function getWhenKeyword() {
        return explode("|", $this->keywords->getWhenKeywords())[0];
    }

    public function getThenKeyword() {
        return explode("|", $this->keywords->getThenKeywords())[0];
    }

    public function getAndKeyword() {
        return explode("|", $this->keywords->getAndKeywords())[0];
    }

    function getLocale() {
        return $this->locale;
    }

    function getKeywords() {
        return $this->keywords;
    }

    function getMink() {
        return $this->mink;
    }

    function setLocale($locale) {
        $this->locale = $locale;
    }

    function setKeywords($keywords) {
        $this->keywords = $keywords;
    }

    function setMink($mink) {
        $this->mink = $mink;
    }

}
