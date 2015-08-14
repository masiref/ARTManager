<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="object", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name", "page_id", "object_type_id"})})
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="object"
 * )
 * @UniqueEntity(
 *      fields={"page"},
 *      groups="object"
 * )
 * @UniqueEntity(
 *      fields={"objectType"},
 *      groups="object"
 * )
 */
class Object implements JsonSerializable {

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
    protected $selected = false;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="objects")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", nullable=true)
     */
    protected $page;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectType")
     * @ORM\JoinColumn(name="object_type_id", referencedColumnName="id", nullable=true)
     */
    protected $objectType;

    /**
     * @ORM\OneToOne(targetEntity="ObjectIdentifier", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="object_identifier_id", referencedColumnName="id")
     */
    protected $objectIdentifier;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    public function __toString() {
        $result = "";
        if ($this->page != null) {
            $result .= $this->page . "\\";
        }
        if ($this->name != null && $this->name != "") {
            $result .= $this->name;
            return $result;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'page' => $this->page,
            'objectType' => $this->objectType,
            'objectIdentifier' => $this->objectIdentifier
        );
    }

    public function getJsonTreeAsArray() {
        $result = array(
            'text' => $this->name . ' <span class="pull-right"><small>' . $this->description . '</small></span>',
            'icon' => $this->objectType->getIcon(),
            'href' => '#node-object-' . $this->id,
            'state' => array(
                'selected' => $this->selected
        ));
        return $result;
    }

    public function getAvailableExecuteActions() {
        $actions = $this->objectType->getActions();
        $result = array();
        foreach ($actions as $action) {
            if ($action->getActionType()->getName() == "Execute") {
                $result[] = $action;
            }
        }
        return $result;
    }

    public function getAvailableControlActions() {
        $actions = $this->objectType->getActions();
        $result = array();
        foreach ($actions as $action) {
            if ($action->getActionType()->getName() == "Control") {
                $result[] = $action;
            }
        }
        return $result;
    }

    public function getHtml() {
        return "<b><i class=\"" . $this->objectType->getIcon() . "\"></i>" . $this->name . "</b>";
    }

    public function getMinkIdentification() {
        $result = "";
        $objectIdentifier = $this->objectIdentifier;
        if ($objectIdentifier != null) {
            $objectIdentifierType = $objectIdentifier->getObjectIdentifierType();
            $objectIdentifierTypeName = $objectIdentifierType->getName();
            $result = $objectIdentifier->getValue();
        } else {
            $result = $this->getName();
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
     * @param \DateTime $createdAt
     * @return Object
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

    function getSelected() {
        return $this->selected;
    }

    function setSelected($selected) {
        $this->selected = $selected;
    }

    /**
     * Set page
     *
     * @param \App\MainBundle\Entity\Page $page
     * @return Object
     */
    public function setPage(\App\MainBundle\Entity\Page $page = null) {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return \App\MainBundle\Entity\Page
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Set objectType
     *
     * @param \App\MainBundle\Entity\ObjectType $objectType
     * @return Object
     */
    public function setObjectType(\App\MainBundle\Entity\ObjectType $objectType = null) {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * Get objectType
     *
     * @return \App\MainBundle\Entity\ObjectType
     */
    public function getObjectType() {
        return $this->objectType;
    }

    /**
     * Set objectIdentifier
     *
     * @param \App\MainBundle\Entity\ObjectIdentifier $objectIdentifier
     * @return Object
     */
    public function setObjectIdentifier(\App\MainBundle\Entity\ObjectIdentifier $objectIdentifier = null) {
        $this->objectIdentifier = $objectIdentifier;

        return $this;
    }

    /**
     * Get objectIdentifier
     *
     * @return \App\MainBundle\Entity\ObjectIdentifier
     */
    public function getObjectIdentifier() {
        return $this->objectIdentifier;
    }

}
