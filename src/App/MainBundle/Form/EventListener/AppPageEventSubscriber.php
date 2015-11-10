<?php

namespace App\MainBundle\Form\EventListener;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

class AppPageEventSubscriber implements EventSubscriberInterface {

    private $factory;
    private $em;
    private $slugify;

    public function __construct(FormFactoryInterface $factory, EntityManager $em, Slugify $slugify) {
        $this->factory = $factory;
        $this->em = $em;
        $this->slugify = $slugify;
    }

    public static function getSubscribedEvents() {
        return array(
            FormEvents::PRE_SUBMIT => "preSubmit"
        );
    }

    public function preSubmit(FormEvent $event) {
        $em = $this->em;
        $slugify = $this->slugify;

        $data = $event->getData();
        $pageTypeId = $data["pageType"];
        $pageType = $em->getRepository("AppMainBundle:PageType")->find($pageTypeId);
        if ($pageType->getName() == "Container" && ($data["path"] == null || trim($data["path"]) == "")) {
            $data["path"] = $slugify->slugify($data["name"]);
            $event->setData($data);
        }
    }

}
