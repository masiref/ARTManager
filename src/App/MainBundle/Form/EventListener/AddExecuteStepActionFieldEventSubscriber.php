<?php

namespace App\MainBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

class AddExecuteStepActionFieldEventSubscriber implements EventSubscriberInterface {

    private $factory;
    private $em;
    private $test;

    public function __construct(FormFactoryInterface $factory, EntityManager $em, $test) {
        $this->factory = $factory;
        $this->em = $em;
        $this->test = $test;
    }

    public static function getSubscribedEvents() {
        return array(
            FormEvents::PRE_SET_DATA => "preSetData",
            FormEvents::PRE_SUBMIT => "preSubmit"
        );
    }

    public function addActions($object, $form) {
        $test = $this->test;
        $actions = null === $object ? array() : $object->getAvailableExecuteActions();

        $form->add($this->factory->createNamed('action', 'entity', null, array(
                    'class' => 'AppMainBundle:Action',
                    'empty_value' => '',
                    'auto_initialize' => false,
                    'choices' => $actions,
                    'attr' => array('data-test-id' => $test->getId()),
                    'icon' => 'up-hand'
        )));
    }

    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        $object = $data->getObject();
        $action = $data->getAction();

        if ($action != null) {
            $this->addActions($object, $form);
        }
    }

    public function preSubmit(FormEvent $event) {
        $em = $this->em;

        $data = $event->getData();
        $form = $event->getForm();

        $objectId = $data["object"];
        $businessStepId = isset($data["businessStep"]) ? $data["businessStep"] : null;
        $object = $em->getRepository("AppMainBundle:Object")->find($objectId);

        if ($form->has('action')) {
            $form->remove('action');
            if ($form->has('parameterDatas')) {
                $form->remove('parameterDatas');
            }
        }

        if ($businessStepId == null) {
            $this->addActions($object, $form);
        }
    }

}
