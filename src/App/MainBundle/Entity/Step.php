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

    /**
     * @ORM\ManyToOne(targetEntity="StepSentenceGroup")
     * @ORM\JoinColumn(name="sentence_group_id", referencedColumnName="id", nullable=true)
     */
    protected $sentenceGroup;
    protected $minkSentence;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->controlSteps = new ArrayCollection();
        $this->parameterDatas = new ArrayCollection();
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
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

    public function getSentence($locale = 'en') {
        $result = "";
        if ($this->sentenceGroup !== null) {
            foreach ($this->sentenceGroup->getSentences() as $sentence) {
                if ($sentence->getLocale() == $locale) {
                    $result = $sentence->getSentence();
                }
            }
            foreach ($this->parameterDatas as $parameterData) {
                $parameter = $parameterData->getParameter();
                $placeholder = "%" . $parameter->getPlaceholder() . "%";
                $result = str_replace($placeholder, "<b>" . $parameterData->getValue() . "</b>", $result);
            }
        }
        return $result;
    }

    public function getMinkSentence($locale = 'en') {
        $result = "";
        if ($this->sentenceGroup != null) {
            foreach ($this->sentenceGroup->getSentences() as $sentence) {
                if ($sentence->getLocale() == $locale) {
                    $result = $sentence->getMinkSentence();
                    if ($result == null || $result == "") {
                        $result = $sentence->getSentence();
                    }
                }
            }
            foreach ($this->parameterDatas as $parameterData) {
                $parameter = $parameterData->getParameter();
                $placeholder = "%" . $parameter->getPlaceholder() . "%";
                $result = str_replace($placeholder, $parameterData->getValue(), $result);
            }
        }
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

    /**
     * Set sentenceGroup
     *
     * @param \App\MainBundle\Entity\StepSentenceGroup $sentenceGroup
     * @return Step
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

    function setMinkSentence($minkSentence) {
        $this->minkSentence = $minkSentence;
    }

}
