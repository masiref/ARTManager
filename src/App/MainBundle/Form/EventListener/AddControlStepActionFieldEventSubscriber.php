<?php

namespace App\MainBundle\Form\EventListener;

use App\MainBundle\Entity\ExecuteStep;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

class AddControlStepActionFieldEventSubscriber implements EventSubscriberInterface {

    private $factory;
    private $em;
    private $step;

    public function __construct(FormFactoryInterface $factory, EntityManager $em, ExecuteStep $step) {
        $this->factory = $factory;
        $this->em = $em;
        $this->step = $step;
    }

    public static function getSubscribedEvents() {
        return array(
            FormEvents::PRE_SET_DATA => "preSetData",
            FormEvents::PRE_SUBMIT => "preSubmit"
        );
    }

    public function addActions($page, $object, $form) {
        $step = $this->step;
        if ($page != null) {
            $actions = null === $page ? array() : $page->getAvailableControlActions();
        } else {
            $actions = null === $object ? array() : $object->getAvailableControlActions();
        }

        $form->add($this->factory->createNamed('action', 'entity', null, array(
                    'class' => 'AppMainBundle:Action',
                    'empty_value' => '',
                    'auto_initialize' => false,
                    'choices' => $actions,
                    'attr' => array('data-step-id' => $step->getId())
        )));
    }

    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        $page = $data->getPage();
        $object = $data->getObject();

        if ($page != null && $object == null || $page == null && $object != null) {
            $this->addActions($page, $object, $form);
        }
    }

    public function preSubmit(FormEvent $event) {
        $em = $this->em;

        $data = $event->getData();
        $form = $event->getForm();

        $pageId = $data["page"];
        $page = $em->getRepository("AppMainBundle:Page")->find($pageId);
        $objectId = $data["object"];
        $object = $em->getRepository("AppMainBundle:Object")->find($objectId);

        if ($form->has('action')) {
            $form->remove('action');
            if ($form->has('parameterDatas')) {
                $form->remove('parameterDatas');
            }
        }

        $this->addActions($page, $object, $form);
    }

}
