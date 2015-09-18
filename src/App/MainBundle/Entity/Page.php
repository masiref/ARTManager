<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="page", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="IDX_Unique_Page", columns={"name", "parent_id", "page_type_id"})
 * })
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="page_page"
 * )
 * @UniqueEntity(
 *      fields={"page"},
 *      groups="page_page"
 * )
 * @UniqueEntity(
 *      fields={"pageType"},
 *      groups="page_page"
 * )
 */
class Page implements JsonSerializable {

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
     * @ORM\ManyToOne(targetEntity="ObjectMap", inversedBy="pages")
     * @ORM\JoinColumn(name="object_map_id", referencedColumnName="id")
     */
    protected $objectMap;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="pages")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $page;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="page", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $pages;

    /**
     * @ORM\OneToMany(targetEntity="Object", mappedBy="page", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $objects;

    /**
     * @ORM\ManyToOne(targetEntity="PageType")
     * @ORM\JoinColumn(name="page_type_id", referencedColumnName="id", nullable=true)
     */
    protected $pageType;

    /**
     * @ORM\OneToMany(targetEntity="Test", mappedBy="startingPage", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $testsStartingWith;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *      message = "Path cannot be empty."
     * )
     */
    protected $path;

    /**
     * @ORM\Column(name="deleted", type="boolean")
     */
    protected $deleted = false;

    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected $deletedAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->pages = new ArrayCollection();
        $this->objects = new ArrayCollection();
        $this->testsStartingWith = new ArrayCollection();
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
            'createdAt' => $this->createdAt->format('d/m/Y \a\t H:i:s'),
            'objectMap' => $this->objectMap,
            'pageType' => $this->pageType,
            'path' => $this->path
        );
    }

    public function getPagesCount() {
        $count = $this->pages->count();
        foreach ($this->pages as $page) {
            $count += $page->getPagesCount();
        }
        return $count;
    }

    public function getJsonTreeAsArray() {
        $result = array(
            'text' => $this->name . ' <span class="pull-right"><small>' . $this->description . '</small></span>',
            'icon' => $this->getPageType()->getIcon(),
            'href' => '#node-page-' . $this->id,
            'state' => array(
                'selected' => $this->selected
        ));
        if ($this->pages->count() > 0 || $this->objects->count() > 0) {
            $result["nodes"] = $this->getNodes();
        }
        return $result;
    }

    private function getNodes() {
        $nodes = array();
        foreach ($this->pages as $page) {
            if (!$page->getDeleted()) {
                $nodes[] = $page->getJsonTreeAsArray();
            }
        }
        foreach ($this->objects as $object) {
            if (!$object->getDeleted()) {
                $nodes[] = $object->getJsonTreeAsArray();
            }
        }
        return $nodes;
    }

    public function getRootObjectMap() {
        $objectMap = $this->objectMap;
        $parent = $this->page;
        while ($objectMap == null && $parent != null) {
            $objectMap = $parent->getObjectMap();
            $parent = $parent->getPage();
        }
        return $objectMap;
    }

    public function getObjectsCount() {
        $count = $this->objects->count();
        foreach ($this->pages as $page) {
            $count += $page->getObjectsCount();
        }
        return $count;
    }

    public function getParentName() {
        if ($this->page === null) {
            return $this->objectMap->getName();
        }
        return $this->page->getParentName() . " > " . $this->page->name;
    }

    public function getAvailableControlActions() {
        $actions = $this->pageType->getActions();
        $result = array();
        foreach ($actions as $action) {
            if ($action->getActionType()->getName() == "Control") {
                $result[] = $action;
            }
        }
        return $result;
    }

    public function getHtml() {
        return "<b><i class=\"" . $this->pageType->getIcon() . "\"></i>" . $this->name . "</b>";
    }

    public function getMinkIdentification() {
        $result = "";
        $pageType = $this->pageType;
        if ($pageType->getName() == "Standard") {
            $result = $this->getPath();
        } else {
            if ($pageType->getName() == "Modal") {
                $result = $this->getName();
            }
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
     * @return Page
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
     * @return Page
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
     * @return Page
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
     * Set objectMap
     *
     * @param \App\MainBundle\Entity\ObjectMap $objectMap
     * @return Page
     */
    public function setObjectMap(\App\MainBundle\Entity\ObjectMap $objectMap = null) {
        $this->objectMap = $objectMap;

        return $this;
    }

    /**
     * Get objectMap
     *
     * @return \App\MainBundle\Entity\ObjectMap
     */
    public function getObjectMap() {
        return $this->objectMap;
    }

    /**
     * Set page
     *
     * @param \App\MainBundle\Entity\Page $page
     * @return Page
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
     * Add pages
     *
     * @param \App\MainBundle\Entity\Page $pages
     * @return Page
     */
    public function addPage(\App\MainBundle\Entity\Page $pages) {
        $pages->page = $this;
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

    /**
     * Add objects
     *
     * @param \App\MainBundle\Entity\Object $objects
     * @return Page
     */
    public function addObject(\App\MainBundle\Entity\Object $objects) {
        $objects->setPage($this);
        $this->objects[] = $objects;

        return $this;
    }

    /**
     * Remove objects
     *
     * @param \App\MainBundle\Entity\Object $objects
     */
    public function removeObject(\App\MainBundle\Entity\Object $objects) {
        $this->objects->removeElement($objects);
    }

    /**
     * Get objects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjects() {
        return $this->objects;
    }

    /**
     * Set pageType
     *
     * @param \App\MainBundle\Entity\PageType $pageType
     * @return Page
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
     * Add testsStartingWith
     *
     * @param \App\MainBundle\Entity\Test $testsStartingWith
     * @return Page
     */
    public function addTestsStartingWith(\App\MainBundle\Entity\Test $testsStartingWith) {
        $testsStartingWith->setStartingPage($this);
        $this->testsStartingWith[] = $testsStartingWith;

        return $this;
    }

    /**
     * Remove testsStartingWith
     *
     * @param \App\MainBundle\Entity\Test $testsStartingWith
     */
    public function removeTestsStartingWith(\App\MainBundle\Entity\Test $testsStartingWith) {
        $this->testsStartingWith->removeElement($testsStartingWith);
    }

    /**
     * Get testsStartingWith
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTestsStartingWith() {
        return $this->testsStartingWith;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Page
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Page
     */
    public function setDeleted($deleted) {
        if ($deleted) {
            $this->deletedAt = new \DateTime();
        }
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted() {
        return $this->deleted;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Page
     */
    public function setDeletedAt($deletedAt) {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt() {
        return $this->deletedAt;
    }

}
