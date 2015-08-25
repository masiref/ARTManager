<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="test_set_folder", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="IDX_Unique_Application", columns={"name", "application_id"}),
 *      @ORM\UniqueConstraint(name="IDX_Unique_Test_Set_Folder", columns={"name", "parent_id"})
 * })
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="test_set_folder_application"
 * )
 * @UniqueEntity(
 *      fields={"application"},
 *      groups="test_set_folder_application"
 * )
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="test_set_folder_test_set_folder"
 * )
 * @UniqueEntity(
 *      fields={"testSetFolder"},
 *      groups="test_set_folder_test_set_folder"
 * )
 */
class TestSetFolder implements JsonSerializable {

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
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="testSetFolders")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    /**
     * @ORM\ManyToOne(targetEntity="TestSetFolder", inversedBy="testSetFolders")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $testSetFolder;

    /**
     * @ORM\OneToMany(targetEntity="TestSetFolder", mappedBy="testSetFolder", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $testSetFolders;

    /**
     * @ORM\OneToMany(targetEntity="TestSet", mappedBy="testSetFolder", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $testSets;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->testSetFolders = new ArrayCollection();
        $this->testSets = new ArrayCollection();
    }

    public function __toString() {
        $result = "";
        if ($this->testSetFolder != null) {
            $result .= $this->testSetFolder . "\\";
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
            'application' => $this->application,
            'chart' => $this->getChartAsArray()
        );
    }

    public function getTestSetFoldersCount() {
        $count = $this->testSetFolders->count();
        foreach ($this->testSetFolders as $testSetFolder) {
            $count += $testSetFolder->getTestSetFoldersCount();
        }
        return $count;
    }

    public function getTestSetsCount() {
        $count = $this->getTestSets()->count();
        foreach ($this->testSetFolders as $testSetFolder) {
            $count += $testSetFolder->getTestSetsCount();
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
        if ($this->testSetFolders->count() > 0 || $this->testSets->count() > 0) {
            $result["nodes"] = $this->getNodes();
        }
        return $result;
    }

    private function getNodes() {
        $nodes = array();
        foreach ($this->testSetFolders as $testSetFolder) {
            $nodes[] = $testSetFolder->getJsonTreeAsArray();
        }
        foreach ($this->testSets as $testSet) {
            $nodes[] = $testSet->getJsonTreeAsArray();
        }
        return $nodes;
    }

    public function getRootApplication() {
        $application = $this->application;
        $parent = $this->testSetFolder;
        while ($application == null && $parent != null) {
            $application = $parent->getApplication();
            $parent = $parent->getTestSetFolder();
        }
        return $application;
    }

    public function getParentName() {
        if ($this->testSetFolder === null) {
            return $this->name;
        }
        return $this->testSetFolder->getParentName() . " > " . $this->name;
    }

    public function getChartAsArray() {
        $result = array(
            array(
                'value' => count($this->getPassedTestInstances()),
                'color' => '#3C763D',
                'highlight' => '#DFF0D8',
                'label' => 'Passed'
            ),
            array(
                'value' => count($this->getFailedTestInstances()),
                'color' => '#A94442',
                'highlight' => '#F2DEDE',
                'label' => 'Failed'
            ),
            array(
                'value' => count($this->getNotCompletedTestInstances()),
                'color' => '#8A6D3B',
                'highlight' => '#FCF8E3',
                'label' => 'Not Completed'
            ),
            array(
                'value' => count($this->getNotRunnedTestInstances()),
                'color' => '#31708F',
                'highlight' => '#D9EDF7',
                'label' => 'Not Runned'
            )
        );
        return $result;
    }

    public function getTestInstancesFilteredByStatus($status) {
        $result = array();
        foreach ($this->testSets as $testSet) {
            $result = array_merge($result, $testSet->getTestInstancesFilteredByStatus($status));
        }
        foreach ($this->testSetFolders as $testSetFolder) {
            $result = array_merge($result, $testSetFolder->getTestInstancesFilteredByStatus($status));
        }
        return $result;
    }

    public function getPassedTestInstances() {
        return $this->getTestInstancesFilteredByStatus("Passed");
    }

    public function getFailedTestInstances() {
        return $this->getTestInstancesFilteredByStatus("Failed");
    }

    public function getNotCompletedTestInstances() {
        return $this->getTestInstancesFilteredByStatus("Not Completed");
    }

    public function getNotRunnedTestInstances() {
        return $this->getTestInstancesFilteredByStatus("Not Runned");
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
     * @return TestSetFolder
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
     * @return TestSetFolder
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
     * @return TestSetFolder
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
     * Set application
     *
     * @param \App\MainBundle\Entity\Application $application
     * @return TestSetFolder
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
     * Set testSetFolder
     *
     * @param \App\MainBundle\Entity\TestSetFolder $testSetFolder
     * @return TestSetFolder
     */
    public function setTestSetFolder(\App\MainBundle\Entity\TestSetFolder $testSetFolder = null) {
        $this->testSetFolder = $testSetFolder;

        return $this;
    }

    /**
     * Get testSetFolder
     *
     * @return \App\MainBundle\Entity\TestSetFolder
     */
    public function getTestSetFolder() {
        return $this->testSetFolder;
    }

    /**
     * Add testSetFolders
     *
     * @param \App\MainBundle\Entity\TestSetFolder $testSetFolders
     * @return TestSetFolder
     */
    public function addTestSetFolder(\App\MainBundle\Entity\TestSetFolder $testSetFolders) {
        $testSetFolders->setTestSetFolder($this);
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
     * Add testSets
     *
     * @param \App\MainBundle\Entity\TestSet $testSets
     * @return TestSetFolder
     */
    public function addTestSet(\App\MainBundle\Entity\TestSet $testSets) {
        $testSets->setTestSetFolder($this);
        $testSets->setApplication($this->getRootApplication());
        $this->testSets[] = $testSets;

        return $this;
    }

    /**
     * Remove testSets
     *
     * @param \App\MainBundle\Entity\TestSet $testSets
     */
    public function removeTestSet(\App\MainBundle\Entity\TestSet $testSets) {
        $this->testSets->removeElement($testSets);
    }

    /**
     * Get testSets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTestSets() {
        return $this->testSets;
    }

    function getSelected() {
        return $this->selected;
    }

    function setSelected($selected) {
        $this->selected = $selected;
    }

}
