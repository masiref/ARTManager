<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="action", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name"})})
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="action"
 * )
 */
class Action implements JsonSerializable {

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
    protected $description;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="ObjectType", inversedBy="actions")
     * @ORM\JoinTable(name="action_object_type",
     *      joinColumns={@ORM\JoinColumn(name="action_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="object_type_id", referencedColumnName="id")}
     * )
     */
    protected $objectTypes;

    /**
     * @ORM\ManyToMany(targetEntity="PageType", inversedBy="actions")
     * @ORM\JoinTable(name="action_page_type",
     *      joinColumns={@ORM\JoinColumn(name="action_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="page_type_id", referencedColumnName="id")}
     * )
     */
    protected $pageTypes;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->objectTypes = new ArrayCollection();
        $this->pageTypes = new ArrayCollection();
    }

    public function __toString() {
        $result = "";
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
            'tokens' => array(),
            'value' => $this->name
        );
    }

    public function getAvailableObjects($page) {
        $objects = $page->getObjects();
        $objectTypes = $this->objectTypes;
        $filteredObjects = array();
        foreach ($objects as $object) {
            if ($objectTypes->contains($object->getObjectType())) {
                $filteredObjects[] = $object;
            }
        }
        return $filteredObjects;
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
     * @return Step
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

    /**
     * Add objectTypes
     *
     * @param \App\MainBundle\Entity\ObjectType $objectTypes
     * @return Action
     */
    public function addObjectType(\App\MainBundle\Entity\ObjectType $objectTypes) {
        $this->objectTypes[] = $objectTypes;

        return $this;
    }

    /**
     * Remove objectTypes
     *
     * @param \App\MainBundle\Entity\ObjectType $objectTypes
     */
    public function removeObjectType(\App\MainBundle\Entity\ObjectType $objectTypes) {
        $this->objectTypes->removeElement($objectTypes);
    }

    /**
     * Get objectTypes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjectTypes() {
        return $this->objectTypes;
    }

    /**
     * Add pageTypes
     *
     * @param \App\MainBundle\Entity\PageType $pageTypes
     * @return Action
     */
    public function addPageType(\App\MainBundle\Entity\PageType $pageTypes) {
        $this->pageTypes[] = $pageTypes;

        return $this;
    }

    /**
     * Remove pageTypes
     *
     * @param \App\MainBundle\Entity\PageType $pageTypes
     */
    public function removePageType(\App\MainBundle\Entity\PageType $pageTypes) {
        $this->pageTypes->removeElement($pageTypes);
    }

    /**
     * Get pageTypes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPageTypes() {
        return $this->pageTypes;
    }

}
