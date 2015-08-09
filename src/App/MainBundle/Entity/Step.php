<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap({"execute" = "ExecuteStep", "control" = "ControlStep"})
 * @ORM\Table(name="step", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="IDX_Unique_Test", columns={"_order", "test_id"}),
 *      @ORM\UniqueConstraint(name="IDX_Unique_Step", columns={"_order", "parent_step_id"})
 * })
 * @UniqueEntity(
 *      fields={"order"},
 *      message="Order already used.",
 *      groups="step_test"
 * )
 * @UniqueEntity(
 *      fields={"test"},
 *      groups="step_test"
 * )
 * @UniqueEntity(
 *      fields={"order"},
 *      message="Order already used.",
 *      groups="step_step"
 * )
 * @UniqueEntity(
 *      fields={"parentStep"},
 *      groups="step_step"
 * )
 */
class Step implements JsonSerializable {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="_order", type="integer")
     */
    protected $order;

    /**
     * @ORM\Column(name="_status", type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="Step", mappedBy="parentStep", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $controlSteps;    // ExecuteStep can have one to many ControlStep

    /**
     * @ORM\ManyToOne(targetEntity="Test", inversedBy="steps")
     * @ORM\JoinColumn(name="test_id", referencedColumnName="id", nullable=true)
     */
    protected $test;            // ExecuteStep must be contained in one Test

    /**
     * @ORM\ManyToOne(targetEntity="Step", inversedBy="controlSteps")
     * @ORM\JoinColumn(name="parent_step_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $parentStep;      // ControlStep must be linked to one ExecuteStep

    /**
     * @ORM\OneToMany(targetEntity="ParameterData", mappedBy="step", cascade={"all"}, orphanRemoval=true)
     */
    protected $parameterDatas;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->controlSteps = new ArrayCollection();
        $this->parameterDatas = new ArrayCollection();
    }

    public function __toString() {
        if ($this->name != null && $this->name != "") {
            return $this->name;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'order' => $this->order,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s')
        );
    }

    public function clearParameterDatas() {
        $this->parameterDatas->clear();
    }

    public function getControlStepAt($order) {
        foreach ($this->controlSteps as $controlStep) {
            if ($controlStep->getOrder() == $order) {
                return $controlStep;
            }
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
     * @return Step
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
     * @return Step
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
     * @return Step
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
     * @return Step
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
     * @return Step
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
     * @return Step
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
     * @param \App\MainBundle\Entity\Step $controlSteps
     * @return Step
     */
    public function addControlStep(\App\MainBundle\Entity\Step $controlSteps) {
        $controlSteps->setOrder($this->controlSteps->count() + 1);
        $controlSteps->setParentStep($this);
        $this->controlSteps[] = $controlSteps;

        return $this;
    }

    /**
     * Remove controlSteps
     *
     * @param \App\MainBundle\Entity\Step $controlSteps
     */
    public function removeControlStep(\App\MainBundle\Entity\Step $controlSteps) {
        $this->controlSteps->removeElement($controlSteps);
    }

    /**
     * Get controlSteps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getControlSteps() {
        return $this->controlSteps;
    }

    /**
     * Set parentStep
     *
     * @param \App\MainBundle\Entity\Step $parentStep
     * @return Step
     */
    public function setParentStep(\App\MainBundle\Entity\Step $parentStep = null) {
        $this->parentStep = $parentStep;

        return $this;
    }

    /**
     * Get parentStep
     *
     * @return \App\MainBundle\Entity\Step
     */
    public function getParentStep() {
        return $this->parentStep;
    }

    /**
     * Add parameterDatas
     *
     * @param \App\MainBundle\Entity\ParameterData $parameterDatas
     * @return Step
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
