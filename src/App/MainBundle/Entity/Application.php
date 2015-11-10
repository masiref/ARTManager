<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="application", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name", "project_id"})})
 * @UniqueEntity(
 *      fields={"name", "project"},
 *      message="Name already used in this project.",
 *      groups="application"
 * )
 */
class Application implements JsonSerializable {

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
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     */
    protected $url;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="ObjectMap", mappedBy="application", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $objectMaps;

    /**
     * @ORM\OneToMany(targetEntity="TestFolder", mappedBy="application", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $testFolders;

    /**
     * @ORM\OneToMany(targetEntity="BusinessStepFolder", mappedBy="application", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $businessStepFolders;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="applications")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    protected $project;

    /**
     * @ORM\OneToMany(targetEntity="TestSetFolder", mappedBy="application", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $testSetFolders;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->objectMaps = new ArrayCollection();
        $this->testFolders = new ArrayCollection();
        $this->testSetFolders = new ArrayCollection();
        $this->businessStepFolders = new ArrayCollection();
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
            'url' => $this->url,
            'project' => $this->project
        );
    }

    public function getPagesCount() {
        $result = 0;
        foreach ($this->objectMaps as $objectMap) {
            $result += $objectMap->getPagesCount();
        }
        return $result;
    }

    public function getObjectsCount() {
        $result = 0;
        foreach ($this->objectMaps as $objectMap) {
            $result += $objectMap->getObjectsCount();
        }
        return $result;
    }

    public function getTestFoldersCount() {
        $count = $this->testFolders->count();
        foreach ($this->testFolders as $testFolder) {
            $count += $testFolder->getTestFoldersCount();
        }
        return $count;
    }

    public function getTestsCount() {
        $count = 0;
        foreach ($this->testFolders as $testFolder) {
            $count += $testFolder->getTestsCount();
        }
        return $count;
    }

    public function getJsonTestsTreeAsArray() {
        $result = array();
        foreach ($this->testFolders as $testFolder) {
            $result[] = $testFolder->getJsonTreeAsArray();
        }
        return $result;
    }

    public function getTestSetFoldersCount() {
        $count = $this->testSetFolders->count();
        foreach ($this->testSetFolders as $testSetFolder) {
            $count += $testSetFolder->getTestSetFoldersCount();
        }
        return $count;
    }

    public function getTestSetsCount() {
        $count = 0;
        foreach ($this->testSetFolders as $testSetFolder) {
            $count += $testSetFolder->getTestSetsCount();
        }
        return $count;
    }

    public function getJsonTestSetsTreeAsArray() {
        $result = array();
        foreach ($this->testSetFolders as $testSetFolder) {
            $result[] = $testSetFolder->getJsonTreeAsArray();
        }
        return $result;
    }

    public function getBusinessStepFoldersCount() {
        $count = $this->businessStepFolders->count();
        foreach ($this->businessStepFolders as $businessStepFolder) {
            $count += $businessStepFolder->getBusinessStepFoldersCount();
        }
        return $count;
    }

    public function getBusinessStepsCount() {
        $count = 0;
        foreach ($this->businessStepFolders as $businessStepFolder) {
            $count += $businessStepFolder->getBusinessStepsCount();
        }
        return $count;
    }

    public function getJsonBusinessStepsTreeAsArray() {
        $result = array();
        foreach ($this->businessStepFolders as $businessStepFolder) {
            $result[] = $businessStepFolder->getJsonTreeAsArray();
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
     * @return Application
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
     * @return Application
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
     * @return Application
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
     * Set project
     *
     * @param \App\MainBundle\Entity\Project $project
     * @return Application
     */
    public function setProject(\App\MainBundle\Entity\Project $project = null) {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \App\MainBundle\Entity\Project
     */
    public function getProject() {
        return $this->project;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Application
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Add objectMaps
     *
     * @param \App\MainBundle\Entity\ObjectMap $objectMaps
     * @return Application
     */
    public function addObjectMap(\App\MainBundle\Entity\ObjectMap $objectMaps) {
        $objectMaps->setApplication($this);
        $this->objectMaps[] = $objectMaps;

        return $this;
    }

    /**
     * Remove objectMaps
     *
     * @param \App\MainBundle\Entity\ObjectMap $objectMaps
     */
    public function removeObjectMap(\App\MainBundle\Entity\ObjectMap $objectMaps) {
        $this->objectMaps->removeElement($objectMaps);
    }

    /**
     * Get objectMaps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjectMaps() {
        return $this->objectMaps;
    }

    /**
     * Add testFolders
     *
     * @param \App\MainBundle\Entity\TestFolder $testFolders
     * @return Application
     */
    public function addTestFolder(\App\MainBundle\Entity\TestFolder $testFolders) {
        $testFolders->setApplication($this);
        $this->testFolders[] = $testFolders;

        return $this;
    }

    /**
     * Remove testFolders
     *
     * @param \App\MainBundle\Entity\TestFolder $testFolders
     */
    public function removeTestFolder(\App\MainBundle\Entity\TestFolder $testFolders) {
        $this->testFolders->removeElement($testFolders);
    }

    /**
     * Get testFolders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTestFolders() {
        return $this->testFolders;
    }

    /**
     * Add testSetFolders
     *
     * @param \App\MainBundle\Entity\TestSetFolder $testSetFolders
     * @return Application
     */
    public function addTestSetFolder(\App\MainBundle\Entity\TestSetFolder $testSetFolders) {
        $testSetFolders->setApplication($this);
        $this->testSetFolders[] = $testSetFolders;

        return $this;
    }

    /**
     * Remove testSetFolders
     *
     * @param \App\MainBundle\Entity\TestSetFolder $testSetFolders
     */
    public function removeTestSetFolder(\App\MainBundle\Entity\TestSetFolder $testSetFolders) {
        $this->testSetFolders->removeElement($testSetFolders);
    }

    /**
     * Get testSetFolders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTestSetFolders() {
        return $this->testSetFolders;
    }

    /**
     * Add businessStepFolders
     *
     * @param \App\MainBundle\Entity\BusinessStepFolder $businessStepFolders
     * @return Application
     */
    public function addBusinessStepFolder(\App\MainBundle\Entity\BusinessStepFolder $businessStepFolders) {
        $businessStepFolders->setApplication($this);
        $this->businessStepFolders[] = $businessStepFolders;

        return $this;
    }

    /**
     * Remove businessStepFolders
     *
     * @param \App\MainBundle\Entity\BusinessStepFolder $businessStepFolders
     */
    public function removeBusinessStepFolder(\App\MainBundle\Entity\BusinessStepFolder $businessStepFolders) {
        $this->businessStepFolders->removeElement($businessStepFolders);
    }

    /**
     * Get businessStepFolders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBusinessStepFolders() {
        return $this->businessStepFolders;
    }

}
