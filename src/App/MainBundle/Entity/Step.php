<?php

namespace App\MainBundle\Entity;

use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap({"execute" = "ExecuteStep", "control" = "ControlStep"})
 * @ORM\Table(name="step")
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
    protected $test;            // ExecuteStep must be contained in a Test or a Business Step

    /**
     * @ORM\ManyToOne(targetEntity="BusinessStep", inversedBy="steps")
     * @ORM\JoinColumn(name="business_step_id", referencedColumnName="id", nullable=true)
     */
    protected $businessStep;    // ExecuteStep must be contained in a Test or a Business Step

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
     * @ORM\JoinColumn(name="sentence_group_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
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
                $value = $parameterData->getValue();
                if (substr($value, 0, 1) === "/") {
                    $value = "&lt;" . substr($value, 1) . "&gt;";
                }
                $result = str_replace($placeholder, "<b>" . $value . "</b>", $result);
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
                $value = $parameterData->getValue();
                if (substr($value, 0, 1) === "/") {
                    $slugify = new Slugify();
                    $value = "%" . $slugify->slugify(substr($value, 1)) . "%";
                }
                $result = str_replace($placeholder, $value, $result);
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
     * @param Step $controlSteps
     * @return Step
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
     * @return Step
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
     * Add parameterDatas
     *
     * @param ParameterData $parameterDatas
     * @return Step
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
     * @param StepSentenceGroup $sentenceGroup
     * @return Step
     */
    public function setSentenceGroup(StepSentenceGroup $sentenceGroup = null) {
        $this->sentenceGroup = $sentenceGroup;

        return $this;
    }

    /**
     * Get sentenceGroup
     *
     * @return StepSentenceGroup
     */
    public function getSentenceGroup() {
        return $this->sentenceGroup;
    }

    function setMinkSentence($minkSentence) {
        $this->minkSentence = $minkSentence;
    }

    /**
     * Set businessStep
     *
     * @param BusinessStep $businessStep
     * @return Step
     */
    public function setBusinessStep(BusinessStep $businessStep = null) {
        $this->businessStep = $businessStep;

        return $this;
    }

    /**
     * Get businessStep
     *
     * @return BusinessStep
     */
    public function getBusinessStep() {
        return $this->businessStep;
    }

}
