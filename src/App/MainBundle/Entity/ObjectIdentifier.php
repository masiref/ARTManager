<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="object_identifier")
 */
class ObjectIdentifier implements JsonSerializable {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectIdentifierType")
     * @ORM\JoinColumn(name="object_identifier_type_id", referencedColumnName="id", nullable=true)
     */
    protected $objectIdentifierType;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    public function __toString() {
        $result = "";
        if ($this->value != null && $this->value != "") {
            $result .= $this->value;
            return $result;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'value' => $this->value,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'objectIdentifierType' => $this->objectIdentifierType
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
     * @return ObjectIdentifier
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
     * @return ObjectIdentifier
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
     * Set objectIdentifierType
     *
     * @param \App\MainBundle\Entity\ObjectIdentifierType $objectIdentifierType
     * @return ObjectIdentifier
     */
    public function setObjectIdentifierType(\App\MainBundle\Entity\ObjectIdentifierType $objectIdentifierType = null) {
        $this->objectIdentifierType = $objectIdentifierType;

        return $this;
    }

    /**
     * Get objectIdentifierType
     *
     * @return \App\MainBundle\Entity\ObjectIdentifierType
     */
    public function getObjectIdentifierType() {
        return $this->objectIdentifierType;
    }

}
