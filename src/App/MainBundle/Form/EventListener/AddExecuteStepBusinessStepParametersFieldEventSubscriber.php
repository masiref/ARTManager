<?php

namespace App\MainBundle\Form\EventListener;

use App\MainBundle\Entity\ParameterData;
use App\MainBundle\Form\Type\ParameterDataType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

class AddExecuteStepBusinessStepParametersFieldEventSubscriber implements EventSubscriberInterface {

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

    public function addParameterDatas($businessStep, $step, $form) {
        $em = $this->em;

        $step->clearParameterDatas();
        $parameterSet = $em->getRepository("AppMainBundle:ParameterSet")
                ->findByBusinessStep($businessStep);
        if ($parameterSet !== null) {
            $parameters = $parameterSet->getParameters();
            $hasParameters = $parameters->count() > 0;
            foreach ($parameters as $parameter) {
                $parameterData = new ParameterData($parameter);
                $step->addParameterData($parameterData);
            }
            if ($hasParameters) {
                $this->addParameterDatasField($form);
            }
        }
    }

    public function addParameterDatasField($form) {
        $form->add('parameterDatas', 'collection', array(
            'type' => new ParameterDataType(),
            'by_reference' => false,
            'options' => array('label' => false),
            'icon' => 'wrench'
        ));
    }

    public function preSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
        $parameterDatas = $data->getParameterDatas();

        if ($parameterDatas->count() > 0) {
            $this->addParameterDatasField($form);
        }
    }

    public function preSubmit(FormEvent $event) {
        $em = $this->em;

        $data = $event->getData();
        $form = $event->getForm();
        $step = $form->getData();

        $businessStepId = isset($data["businessStep"]) ? $data["businessStep"] : null;
        if ($businessStepId !== null) {
            $businessStep = $em->getRepository("AppMainBundle:BusinessStep")->find($businessStepId);
            $this->addParameterDatas($businessStep, $step, $form);
        }
    }

}
