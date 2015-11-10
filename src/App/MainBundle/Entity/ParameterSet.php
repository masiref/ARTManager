<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="ParameterSetRepository")
 * @ORM\Table(name="parameter_set", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="IDX_Unique_Object_Type", columns={"action_id", "object_type_id"}),
 *      @ORM\UniqueConstraint(name="IDX_Unique_Page_Type", columns={"action_id", "page_type_id"})
 * })
 */
class ParameterSet implements JsonSerializable {

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
     * @ORM\OneToOne(targetEntity="BusinessStep", mappedBy="parameterSet", cascade={"persist"})
     * @ORM\JoinColumn(name="business_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $businessStep;

    /**
     * @ORM\OneToMany(targetEntity="Parameter", mappedBy="parameterSet", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $parameters;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->parameters = new ArrayCollection();
    }

    public function __toString() {
        if ($this->action != null) {
            $result = $this->action . " / ";
            if ($this->objectType != null) {
                return $result . $this->objectType;
            }
            if ($this->pageType != null) {
                return $result . $this->pageType;
            }
        }
        if ($this->businessStep != null) {
            return $this->businessStep . "";
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
     * Add parameters
     *
     * @param Parameter $parameters
     * @return ParameterSet
     */
    public function addParameter(Parameter $parameters) {
        $parameters->setOrder($this->parameters->count() + 1);
        $parameters->setParameterSet($this);
        $this->parameters[] = $parameters;

        return $this;
    }

    /**
     * Remove parameters
     *
     * @param Parameter $parameters
     */
    public function removeParameter(Parameter $parameters) {
        $this->parameters->removeElement($parameters);
    }

    /**
     * Get parameters
     *
     * @return Collection
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Set pageType
     *
     * @param \App\MainBundle\Entity\PageType $pageType
     * @return ParameterSet
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

    /**
     * Set businessStep
     *
     * @param \App\MainBundle\Entity\BusinessStep $businessStep
     * @return ParameterSet
     */
    public function setBusinessStep(\App\MainBundle\Entity\BusinessStep $businessStep = null) {
        $this->businessStep = $businessStep;

        return $this;
    }

    /**
     * Get businessStep
     *
     * @return \App\MainBundle\Entity\BusinessStep
     */
    public function getBusinessStep() {
        return $this->businessStep;
    }

}
