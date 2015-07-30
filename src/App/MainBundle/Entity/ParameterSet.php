<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="parameter_set", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name", "action_id", "object_type_id"})})
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="parameter_set"
 * )
 * @UniqueEntity(
 *      fields={"action"},
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
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *      message = "Name cannot be empty."
     * )
     * @Assert\Length(
     *      min = "3",
     *      max = "50",
     *      minMessage = "Name is too short. It should have {{ limit }} characters or more.",
     *      maxMessage = "Name is too long. It should have {{ limit }} characters or less."
     * )
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description = "";

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
     * Set name
     *
     * @param string $name
     * @return Object
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
     * @return Object
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
     * @param \App\MainBundle\Entity\Parameter $parameters
     * @return ParameterSet
     */
    public function addParameter(\App\MainBundle\Entity\Parameter $parameters) {
        $parameters->setOrder($this->parameters->count() + 1);
        $parameters->setParameterSet($this);
        $this->parameters[] = $parameters;

        return $this;
    }

    /**
     * Remove parameters
     *
     * @param \App\MainBundle\Entity\Parameter $parameters
     */
    public function removeParameter(\App\MainBundle\Entity\Parameter $parameters) {
        $this->parameters->removeElement($parameters);
    }

    /**
     * Get parameters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParameters() {
        return $this->parameters;
    }

}
