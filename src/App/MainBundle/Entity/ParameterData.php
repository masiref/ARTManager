<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\MainBundle\Entity\ParameterDataRepository")
 * @ORM\Table(name="parameter_data")
 */
class ParameterData implements JsonSerializable {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotNull
     */
    protected $value;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Step", inversedBy="parameterDatas")
     * @ORM\JoinColumn(name="step_id", referencedColumnName="id")
     */
    protected $step;

    /**
     * @ORM\ManyToOne(targetEntity="Parameter")
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id")
     */
    protected $parameter;

    public function __construct(Parameter $parameter) {
        $this->createdAt = new DateTime();
        $this->parameter = $parameter;
    }

    public function __toString() {
        if ($this->value != null && $this->value != "") {
            return $this->parameter . " = " . $this->value;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'value' => $this->value,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'step' => $this->step,
            'parameter' => $this->parameter
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
     * Set value
     *
     * @param string $value
     * @return ParameterData
     */
    public function setValue($value) {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ParameterData
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
     * Set step
     *
     * @param \App\MainBundle\Entity\Step $step
     * @return ParameterData
     */
    public function setStep(\App\MainBundle\Entity\Step $step = null) {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \App\MainBundle\Entity\Step
     */
    public function getStep() {
        return $this->step;
    }

    /**
     * Set parameter
     *
     * @param \App\MainBundle\Entity\Parameter $parameter
     * @return ParameterData
     */
    public function setParameter(\App\MainBundle\Entity\Parameter $parameter = null) {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Get parameter
     *
     * @return \App\MainBundle\Entity\Parameter
     */
    public function getParameter() {
        return $this->parameter;
    }

}
