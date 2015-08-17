<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="execute_step")
 * @ORM\HasLifecycleCallbacks()
 */
class ExecuteStep extends Step {

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $order;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var Test
     */
    protected $test;

    /**
     * @var Collection
     */
    protected $controlSteps;

    /**
     * @var Step
     */
    protected $parentStep;

    /**
     * @ORM\ManyToOne(targetEntity="Action")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $action;

    /**
     * @ORM\ManyToOne(targetEntity="Object")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $object;

    /**
     * @var Collection
     */
    protected $parameterDatas;

    /**
     * @var \App\MainBundle\Entity\StepSentenceGroup
     */
    protected $sentenceGroup;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->controlSteps = new ArrayCollection();
    }

    public function __toString() {
        $result = "";
        if ($this->object != null && $this->action != "") {
            $result = $this->object->getName() . " " . $this->action;
            if ($this->parameterDatas->count() > 0) {
                foreach ($this->parameterDatas as $parameterData) {
                    $result .= " " . $parameterData;
                }
            }
            return $result;
        }
        return "New";
    }

    public function getActivePage() {
        $page = null;
        if ($this->order >= 1) {
            $lastControlStep = null;
            if ($this->controlSteps->count() > 0) {
                $lastControlStep = $this->controlSteps->get($this->controlSteps->count() - 1);
            }
            if ($lastControlStep !== null) {
                $page = $lastControlStep->getActivePage();
            }
            $order = $this->order - 1;
            while ($page == null && $order > 0) {
                $page = $this->test->getPageAtStepOrder($order);
                $order -= 1;
            }
        }
        if ($page === null) {
            $page = $this->test->getStartingPage();
        }
        return $page;
    }

    public function getPageAtControlStepOrder($order) {
        $page = null;
        if ($order > 0) {
            $controlStep = null;
            foreach ($this->controlSteps as $controlStep) {
                if ($controlStep->getOrder() === $order) {
                    break;
                }
            }
            if ($controlStep !== null) {
                $page = $controlStep->getActivePage();
            }
        }
        if ($page === null) {
            $page = $this->test->getPageAtStepOrder($this->order - 1);
        }
        return $page;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function updateSentenceGroup(LifecycleEventArgs $event) {
        $oldAction = null;
        $oldObjectType = null;
        $oldSentenceGroup = $this->sentenceGroup;
        if ($oldSentenceGroup !== null) {
            $oldAction = $oldSentenceGroup->getAction();
            $oldObjectType = $oldSentenceGroup->getObjectType();
        }
        $action = $this->action;
        $objectType = $this->object->getObjectType();
        if ($oldAction != $action || $oldObjectType != $objectType) {
            $em = $event->getEntityManager();
            $sentenceGroup = $em->getRepository("AppMainBundle:StepSentenceGroup")
                    ->findByObjectTypeAndAction($objectType, $action);
            $this->sentenceGroup = $sentenceGroup;
        }
    }

    public function getSentence($locale = 'en') {
        $result = str_replace("\"%object%\"", $this->object->getHtml(), parent::getSentence($locale));
        return $result;
    }

    public function getMinkSentence($locale = 'en') {
        $result = str_replace("%object%", $this->object->getMinkIdentification(), parent::getMinkSentence($locale));
        return $result;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set order
     *
     * @param integer $order
     * @return ExecuteStep
     */
    public function setOrder($order) {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return ExecuteStep
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     * @return ExecuteStep
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set test
     *
     * @param Test $test
     * @return ExecuteStep
     */
    public function setTest(Test $test = null) {
        $this->test = $test;

        return $this;
    }

    /**
     * Get test
     *
     * @return Test
     */
    public function getTest() {
        return $this->test;
    }

    /**
     * Add controlSteps
     *
     * @param Step $controlSteps
     * @return ExecuteStep
     */
    public function addControlStep(Step $controlSteps) {
        $controlSteps->setOrder($this->controlSteps->count() + 1);
        $controlSteps->setParentStep($this);
        $this->controlSteps[] = $controlSteps;

        return $this;
    }

    /**
     * Remove controlSteps
     *
     * @param Step $controlSteps
     */
    public function removeControlStep(Step $controlSteps) {
        $this->controlSteps->removeElement($controlSteps);
    }

    /**
     * Get controlSteps
     *
     * @return Collection
     */
    public function getControlSteps() {
        return $this->controlSteps;
    }

    /**
     * Set parentStep
     *
     * @param Step $parentStep
     * @return ExecuteStep
     */
    public function setParentStep(Step $parentStep = null) {
        $this->parentStep = $parentStep;

        return $this;
    }

    /**
     * Get parentStep
     *
     * @return Step
     */
    public function getParentStep() {
        return $this->parentStep;
    }

    /**
     * Set action
     *
     * @param Action $action
     * @return ExecuteStep
     */
    public function setAction(Action $action = null) {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return Action
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set object
     *
     * @param Object $object
     * @return ExecuteStep
     */
    public function setObject(Object $object = null) {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return Object
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * Add parameterDatas
     *
     * @param ParameterData $parameterDatas
     * @return ExecuteStep
     */
    public function addParameterData(ParameterData $parameterDatas) {
        $parameterDatas->setStep($this);
        $this->parameterDatas[] = $parameterDatas;

        return $this;
    }

    /**
     * Remove parameterDatas
     *
     * @param ParameterData $parameterDatas
     */
    public function removeParameterData(ParameterData $parameterDatas) {
        $this->parameterDatas->removeElement($parameterDatas);
    }

    /**
     * Get parameterDatas
     *
     * @return Collection
     */
    public function getParameterDatas() {
        return $this->parameterDatas;
    }

    /**
     * Set sentenceGroup
     *
     * @param \App\MainBundle\Entity\StepSentenceGroup $sentenceGroup
     * @return ExecuteStep
     */
    public function setSentenceGroup(\App\MainBundle\Entity\StepSentenceGroup $sentenceGroup = null) {
        $this->sentenceGroup = $sentenceGroup;

        return $this;
    }

    /**
     * Get sentenceGroup
     *
     * @return \App\MainBundle\Entity\StepSentenceGroup
     */
    public function getSentenceGroup() {
        return $this->sentenceGroup;
    }

}
