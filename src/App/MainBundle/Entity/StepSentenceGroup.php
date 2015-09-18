<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="StepSentenceGroupRepository")
 * @ORM\Table(name="step_sentence_group", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"action_id", "object_type_id"})})
 */
class StepSentenceGroup implements JsonSerializable {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Action")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     */
    protected $action;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectType")
     * @ORM\JoinColumn(name="object_type_id", referencedColumnName="id")
     */
    protected $objectType;

    /**
     * @ORM\ManyToOne(targetEntity="PageType")
     * @ORM\JoinColumn(name="page_type_id", referencedColumnName="id")
     */
    protected $pageType;

    /**
     * @ORM\ManyToMany(targetEntity="StepSentence", inversedBy="groups")
     * @ORM\JoinTable(name="group_step_sentence",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="step_sentence_id", referencedColumnName="id")}
     * )
     */
    protected $sentences;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->sentences = new ArrayCollection();
    }

    public function __toString() {
        if ($this->action != null && $this->objectType != null) {
            return $this->action . " " . $this->objectType;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'action' => $this->action,
            'objectType' => $this->objectType
        );
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
     * Set createdAt
     *
     * @param DateTime $createdAt
     * @return Object
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
     * Set action
     *
     * @param Action $action
     * @return ParameterSet
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
     * Set objectType
     *
     * @param ObjectType $objectType
     * @return ParameterSet
     */
    public function setObjectType(ObjectType $objectType = null) {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * Get objectType
     *
     * @return ObjectType
     */
    public function getObjectType() {
        return $this->objectType;
    }

    /**
     * Add sentences
     *
     * @param StepSentence $sentences
     * @return StepSentenceGroup
     */
    public function addSentence(StepSentence $sentences) {
        $this->sentences[] = $sentences;

        return $this;
    }

    /**
     * Remove sentences
     *
     * @param StepSentence $sentences
     */
    public function removeSentence(StepSentence $sentences) {
        $this->sentences->removeElement($sentences);
    }

    /**
     * Get sentences
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSentences() {
        return $this->sentences;
    }

    /**
     * Set pageType
     *
     * @param \App\MainBundle\Entity\PageType $pageType
     * @return StepSentenceGroup
     */
    public function setPageType(\App\MainBundle\Entity\PageType $pageType = null) {
        $this->pageType = $pageType;

        return $this;
    }

    /**
     * Get pageType
     *
     * @return \App\MainBundle\Entity\PageType
     */
    public function getPageType() {
        return $this->pageType;
    }

}
