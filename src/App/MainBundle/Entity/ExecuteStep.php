<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="execute_step")
 */
class ExecuteStep extends Step {

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

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
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $parameterDatas;

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
        $lastControlStep = $this->controlSteps->get($this->controlSteps->count() - 1);
        if ($lastControlStep !== null) {
            return $lastControlStep->getActivePage();
        }
        return null;
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
     * Set name
     *
     * @param string $name
     * @return ExecuteStep
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ExecuteStep
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
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
     * @param \App\MainBundle\Entity\Action $action
     * @return ExecuteStep
     */
    public function setAction(\App\MainBundle\Entity\Action $action = null) {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \App\MainBundle\Entity\Action
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set object
     *
     * @param \App\MainBundle\Entity\Object $object
     * @return ExecuteStep
     */
    public function setObject(\App\MainBundle\Entity\Object $object = null) {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return \App\MainBundle\Entity\Object
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * Add parameterDatas
     *
     * @param \App\MainBundle\Entity\ParameterData $parameterDatas
     * @return ExecuteStep
     */
    public function addParameterData(\App\MainBundle\Entity\ParameterData $parameterDatas) {
        $parameterDatas->setStep($this);
        $this->parameterDatas[] = $parameterDatas;

        return $this;
    }

    /**
     * Remove parameterDatas
     *
     * @param \App\MainBundle\Entity\ParameterData $parameterDatas
     */
    public function removeParameterData(\App\MainBundle\Entity\ParameterData $parameterDatas) {
        $this->parameterDatas->removeElement($parameterDatas);
    }

    /**
     * Get parameterDatas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParameterDatas() {
        return $this->parameterDatas;
    }

}
