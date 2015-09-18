<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {

    public function __construct($environment, $debug) {
        parent::__construct($environment, $debug);
        date_default_timezone_set('Europe/Paris');
    }

    public function registerBundles() {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\IntlBundle\SonataIntlBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\UserBundle\SonataUserBundle(),
            new Sonata\MediaBundle\SonataMediaBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            //new SimpleThings\EntityAudit\SimpleThingsEntityAuditBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Knp\Bundle\TimeBundle\KnpTimeBundle(),
            new Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Mmoreram\GearmanBundle\GearmanBundle(),
            new SimpleThings\FormExtraBundle\SimpleThingsFormExtraBundle(),
            new Application\Sonata\UserBundle\ApplicationSonataUserBundle(),
            new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(),
            new App\MainBundle\AppMainBundle(),
            new App\UserBundle\AppUserBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

}
