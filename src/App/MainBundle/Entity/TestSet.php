<?php

namespace App\MainBundle\Entity;

use App\MainBundle\Services\GherkinService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="test_set", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name", "test_set_folder_id"})})
 * @UniqueEntity(
 *      fields={"name", "testSetFolder"},
 *      message="Name already used.",
 *      groups="test_set"
 * )
 */
class TestSet implements JsonSerializable {

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
     * @ORM\ManyToOne(targetEntity="TestSetFolder", inversedBy="testSets")
     * @ORM\JoinColumn(name="test_set_folder_id", referencedColumnName="id", nullable=true)
     */
    protected $testSetFolder;

    /**
     * @ORM\ManyToOne(targetEntity="Application")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="TestInstance", mappedBy="testSet", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $testInstances;

    /**
     * @ORM\OneToMany(targetEntity="TestSetRun", mappedBy="testSet", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $runs;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->testInstances = new ArrayCollection();
        $this->runs = new ArrayCollection();
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
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'testSetFolder' => $this->testSetFolder,
            'application' => $this->application,
            'status' => $this->status,
            'chart' => $this->getChartAsArray()
        );
    }

    public function getJsonTreeAsArray() {
        $result = array(
            'text' => $this->name . ' <span class="pull-right"><small>' . $this->description . '</small></span>',
            'icon' => "fontello-icon-beaker",
            'href' => '#node-test-set-' . $this->id,
            'state' => array(
                'selected' => $this->selected
        ));
        return $result;
    }

    public function getParentName() {
        return $this->testSetFolder->getParentName();
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
                'value' => count($this->getQueuedTestInstances()),
                'color' => '#8A6D3B',
                'highlight' => '#FCF8E3',
                'label' => 'Queued'
            ),
            array(
                'value' => count($this->getRunningTestInstances()),
                'color' => '#31708F',
                'highlight' => '#D9EDF7',
                'label' => 'Running'
            )
        );
        return $result;
    }

    public function getTestInstancesFilteredByStatus($status) {
        $result = array();
        foreach ($this->testInstances as $instance) {
            $lastRun = $instance->getLastRun();
            if ($lastRun != null) {
                $lastRunStatus = $lastRun->getStatus();
                if ($lastRunStatus != null && $lastRunStatus->getName() == $status) {
                    $result[] = $instance;
                }
            }
        }
        return $result;
    }

    public function getPassedTestInstances() {
        return $this->getTestInstancesFilteredByStatus("Passed");
    }

    public function getFailedTestInstances() {
        return $this->getTestInstancesFilteredByStatus("Failed");
    }

    public function getQueuedTestInstances() {
        return $this->getTestInstancesFilteredByStatus("Queued");
    }

    public function getRunningTestInstances() {
        return $this->getTestInstancesFilteredByStatus("Running");
    }

    public function getBehatFeature(GherkinService $gherkin) {
        return $gherkin->generateBehatFeature($this);
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
     * @return TestSet
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
     * @return TestSet
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
     * @return TestSet
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
     * Set testSetFolder
     *
     * @param TestSetFolder $testSetFolder
     * @return TestSet
     */
    public function setTestSetFolder(TestSetFolder $testSetFolder = null) {
        $this->testSetFolder = $testSetFolder;

        return $this;
    }

    /**
     * Get testSetFolder
     *
     * @return TestSetFolder
     */
    public function getTestSetFolder() {
        return $this->testSetFolder;
    }

    /**
     * Set application
     *
     * @param Application $application
     * @return TestSet
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

    function getSelected() {
        return $this->selected;
    }

    function setSelected($selected) {
        $this->selected = $selected;
    }

    /**
     * Set status
     *
     * @param Status $status
     * @return TestSet
     */
    public function setStatus(Status $status = null) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return Status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Add testInstances
     *
     * @param TestInstance $testInstances
     * @return TestSet
     */
    public function addTestInstance(TestInstance $testInstances) {
        $testInstances->setTestSet($this);
        $testInstances->setOrder($this->testInstances->count() + 1);
        $this->testInstances[] = $testInstances;

        return $this;
    }

    /**
     * Remove testInstances
     *
     * @param TestInstance $testInstances
     */
    public function removeTestInstance(TestInstance $testInstances) {
        $this->testInstances->removeElement($testInstances);
    }

    /**
     * Get testInstances
     *
     * @return Collection
     */
    public function getTestInstances() {
        return $this->testInstances;
    }

    /**
     * Add runs
     *
     * @param \App\MainBundle\Entity\TestSetRun $runs
     * @return TestSet
     */
    public function addRun(\App\MainBundle\Entity\TestSetRun $runs) {
        $runs->setTestSet($this);
        $this->runs[] = $runs;

        foreach ($this->testInstances as $testInstance) {
            $testRun = new TestRun();
            $testRun->setTestInstance($testInstance);
            $runs->addTestRun($testRun);
        }

        return $this;
    }

    /**
     * Remove runs
     *
     * @param \App\MainBundle\Entity\TestSetRun $runs
     */
    public function removeRun(\App\MainBundle\Entity\TestSetRun $runs) {
        $this->runs->removeElement($runs);
    }

    /**
     * Get runs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRuns() {
        return $this->runs;
    }

}
