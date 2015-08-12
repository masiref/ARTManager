<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="parameter")
 */
class Parameter implements JsonSerializable {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="_order", type="string")
     */
    protected $order;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $mandatory = false;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="ParameterSet", inversedBy="parameters")
     * @ORM\JoinColumn(name="parameter_set_id", referencedColumnName="id")
     */
    protected $parameterSet;

    /**
     * @ORM\Column(type="string")
     */
    protected $placeholder;

    public function __construct() {
        $this->createdAt = new DateTime();
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
            'order' => $this->order,
            'description' => $this->description,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s')
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
     * @return Parameter
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
     * @return Parameter
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
     * @param \DateTime $createdAt
     * @return Parameter
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set order
     *
     * @param string $order
     * @return Parameter
     */
    public function setOrder($order) {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return string
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * Set parameterSet
     *
     * @param \App\MainBundle\Entity\ParameterSet $parameterSet
     * @return Parameter
     */
    public function setParameterSet(\App\MainBundle\Entity\ParameterSet $parameterSet = null) {
        $this->parameterSet = $parameterSet;

        return $this;
    }

    /**
     * Get parameterSet
     *
     * @return \App\MainBundle\Entity\ParameterSet
     */
    public function getParameterSet() {
        return $this->parameterSet;
    }

    /**
     * Set mandatory
     *
     * @param boolean $mandatory
     * @return Parameter
     */
    public function setMandatory($mandatory) {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * Get mandatory
     *
     * @return boolean
     */
    public function getMandatory() {
        return $this->mandatory;
    }

    /**
     * Set placeholder
     *
     * @param string $placeholder
     * @return Parameter
     */
    public function setPlaceholder($placeholder) {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get placeholder
     *
     * @return string
     */
    public function getPlaceholder() {
        return $this->placeholder;
    }

}
