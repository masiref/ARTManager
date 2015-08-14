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
 * @ORM\Table(name="object_map", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name", "application_id"})})
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="object_map"
 * )
 * @UniqueEntity(
 *      fields={"application"},
 *      groups="object_map"
 * )
 */
class ObjectMap implements JsonSerializable {

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
     * @ORM\OneToMany(targetEntity="Page", mappedBy="objectMap", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $pages;

    /**
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="objectMaps")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->pages = new ArrayCollection();
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
            'application' => $this->application
        );
    }

    public function getPagesCount() {
        return $this->pages->count();
    }

    public function getJsonTree() {
        return json_encode($this->getJsonTreeAsArray());
    }

    public function getJsonTreeAsArray() {
        $result = array();
        foreach ($this->pages as $page) {
            if ($page->getPage() == null) {
                $result[] = $page->getJsonTreeAsArray();
            }
        }
        return $result;
    }

    public function getObjectsCount() {
        $count = 0;
        foreach ($this->pages as $page) {
            $count += $page->getObjectsCount();
        }
        return $count;
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
     * @return ObjectMap
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
     * @return ObjectMap
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
     * @return ObjectMap
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
     * Set application
     *
     * @param Application $application
     * @return ObjectMap
     */
    public function setApplication(Application $application = null) {
        $this->application = $application;

        return $this;
    }

    /**
     * Get application
     *
     * @return Application
     */
    public function getApplication() {
        return $this->application;
    }

    /**
     * Add pages
     *
     * @param \App\MainBundle\Entity\Page $pages
     * @return ObjectMap
     */
    public function addPage(\App\MainBundle\Entity\Page $pages) {
        $pages->setObjectMap($this);
        $this->pages[] = $pages;

        return $this;
    }

    /**
     * Remove pages
     *
     * @param \App\MainBundle\Entity\Page $pages
     */
    public function removePage(\App\MainBundle\Entity\Page $pages) {
        $this->pages->removeElement($pages);
    }

    /**
     * Get pages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPages() {
        return $this->pages;
    }

}
