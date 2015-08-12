<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="ParameterSetRepository")
 * @ORM\Table(name="parameter_set", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"action_id", "object_type_id"})})
 * @UniqueEntity(
 *      fields={"action"},
 *      message="Action and Object Type already mapped.",
 *      groups="parameter_set"
 * )
 * @UniqueEntity(
 *      fields={"objectType"},
 *      groups="parameter_set"
 * )
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
     * @ORM\OneToMany(targetEntity="Parameter", mappedBy="parameterSet", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $parameters;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->parameters = new ArrayCollection();
    }

    public function __toString() {
        if ($this->action != null && $this->objectType != null) {
            return $this->action . " / " . $this->objectType;
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

}
