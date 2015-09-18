<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="test_folder", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="IDX_Unique_Application", columns={"name", "application_id"}),
 *      @ORM\UniqueConstraint(name="IDX_Unique_Test_Folder", columns={"name", "parent_id"})
 * })
 * @UniqueEntity(
 *      fields={"name", "application"},
 *      message="Name already used.",
 *      groups="test_folder_application"
 * )
 * @UniqueEntity(
 *      fields={"name", "testFolder"},
 *      message="Name already used.",
 *      groups="test_folder_test_folder"
 * )
 */
class TestFolder implements JsonSerializable {

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
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="testFolders")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    /**
     * @ORM\ManyToOne(targetEntity="TestFolder", inversedBy="testFolders")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $testFolder;

    /**
     * @ORM\OneToMany(targetEntity="TestFolder", mappedBy="testFolder", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $testFolders;

    /**
     * @ORM\OneToMany(targetEntity="Test", mappedBy="testFolder", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $tests;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->testFolders = new ArrayCollection();
        $this->tests = new ArrayCollection();
    }

    public function __toString() {
        $result = "";
        if ($this->testFolder != null) {
            $result .= $this->testFolder . "\\";
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
            'createdAt' => $this->createdAt->format('d/m/Y \a\t H:i:s'),
            'application' => $this->application
        );
    }

    public function getTestFoldersCount() {
        $count = $this->testFolders->count();
        foreach ($this->testFolders as $testFolder) {
            $count += $testFolder->getTestFoldersCount();
        }
        return $count;
    }

    public function getTestsCount() {
        $count = $this->getTests()->count();
        foreach ($this->testFolders as $testFolder) {
            $count += $testFolder->getTestsCount();
        }
        return $count;
    }

    public function getJsonTreeAsArray() {
        $result = array(
            'text' => $this->name . ' <span class="pull-right"><small>' . $this->description . '</small></span>',
            'icon' => 'fontello-icon-folder',
            'href' => '#node-folder-' . $this->id,
            'state' => array(
                'selected' => $this->selected
        ));
        if ($this->testFolders->count() > 0 || $this->tests->count() > 0) {
            $result["nodes"] = $this->getNodes();
        }
        return $result;
    }

    private function getNodes() {
        $nodes = array();
        foreach ($this->testFolders as $testFolder) {
            $nodes[] = $testFolder->getJsonTreeAsArray();
        }
        foreach ($this->tests as $test) {
            $nodes[] = $test->getJsonTreeAsArray();
        }
        return $nodes;
    }

    public function getRootApplication() {
        $application = $this->application;
        $parent = $this->testFolder;
        while ($application == null && $parent != null) {
            $application = $parent->getApplication();
            $parent = $parent->getTestFolder();
        }
        return $application;
    }

    public function getParentName() {
        if ($this->testFolder === null) {
            return $this->name;
        }
        return $this->testFolder->getParentName() . " > " . $this->name;
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
     * @return TestFolder
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
     * @return TestFolder
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
     * @return TestFolder
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
     * Set application
     *
     * @param \App\MainBundle\Entity\Application $application
     * @return TestFolder
     */
    public function setApplication(\App\MainBundle\Entity\Application $application = null) {
        $this->application = $application;

        return $this;
    }

    /**
     * Get application
     *
     * @return \App\MainBundle\Entity\Application
     */
    public function getApplication() {
        return $this->application;
    }

    /**
     * Set testFolder
     *
     * @param \App\MainBundle\Entity\TestFolder $testFolder
     * @return TestFolder
     */
    public function setTestFolder(\App\MainBundle\Entity\TestFolder $testFolder = null) {
        $this->testFolder = $testFolder;

        return $this;
    }

    /**
     * Get testFolder
     *
     * @return \App\MainBundle\Entity\TestFolder
     */
    public function getTestFolder() {
        return $this->testFolder;
    }

    /**
     * Add testFolders
     *
     * @param \App\MainBundle\Entity\TestFolder $testFolders
     * @return TestFolder
     */
    public function addTestFolder(\App\MainBundle\Entity\TestFolder $testFolders) {
        $testFolders->testFolder = $this;
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
     * Add tests
     *
     * @param \App\MainBundle\Entity\Test $tests
     * @return TestFolder
     */
    public function addTest(\App\MainBundle\Entity\Test $tests) {
        $tests->setTestFolder($this);
        $tests->setApplication($this->getRootApplication());
        $this->tests[] = $tests;

        return $this;
    }

    /**
     * Remove tests
     *
     * @param \App\MainBundle\Entity\Test $tests
     */
    public function removeTest(\App\MainBundle\Entity\Test $tests) {
        $this->tests->removeElement($tests);
    }

    /**
     * Get tests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTests() {
        return $this->tests;
    }

}
