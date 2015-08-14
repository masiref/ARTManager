<?php

namespace App\MainBundle\Services;

use App\MainBundle\Entity\Page;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Yaml\Yaml;

class MinkService {

    private $steps;

    public function __construct(Container $container) {
        $yaml = $container->get('kernel')->locateResource("@AppMainBundle/Resources/config/behat/mink/i18n.yml");
        $yamlArray = Yaml::parse($yaml);
        $locale = $container->getParameter("locale");
        if (array_key_exists($locale, $yamlArray)) {
            $this->steps = $yamlArray[$locale];
        } else {
            $this->steps = $yamlArray['en'];
        }
    }

    public function getIAmOnPageStep(Page $page) {
        return str_replace("%page%", $page->getPath(), $this->steps["i_am_on_page"]);
    }

}
