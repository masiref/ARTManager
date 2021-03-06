<?php

namespace App\MainBundle\Entity;

use Cocur\Slugify\Slugify;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="TestSetRunRepository")
 * @ORM\Table(name="test_set_run")
 */
class TestSetRun implements JsonSerializable {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="slug", type="string")
     */
    protected $slug;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="TestSet", inversedBy="runs")
     * @ORM\JoinColumn(name="test_set_id", referencedColumnName="id")
     */
    protected $testSet;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="ExecutionServer")
     * @ORM\JoinColumn(name="execution_server_id", referencedColumnName="id")
     * @Assert\NotNull(
     *      message = "Please select an execution server."
     * )
     */
    protected $executionServer;

    /**
     * @ORM\OneToMany(targetEntity="TestRun", mappedBy="testSetRun", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $testRuns;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(name="gearman_job_handle", type="string", nullable=true)
     */
    protected $gearmanJobHandle;

    /**
     * @ORM\OneToOne(targetEntity="ExecutionReport", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="execution_report_id", referencedColumnName="id", nullable=true)
     */
    protected $executionReport;

    /**
     * @ORM\Column(name="started_at", type="datetime", nullable=true)
     */
    protected $startedAt;

    /**
     * @ORM\Column(name="ended_at", type="datetime", nullable=true)
     */
    protected $endedAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $t = microtime(true);
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $slugify = new Slugify();
        $this->slug = $slugify->slugify(
                $this->createdAt->format('d-m-Y H:i:s')
                . "-" . $this->testSet
                . "-" . $micro
        );
        $this->name = $this->slug;
        $this->testRuns = new ArrayCollection();
    }

    public function __toString() {
        if ($this->testSet != null) {
            $result = $this->testSet . " \\ " . $this->slug;
            return $result;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'slug' => $this->slug,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'testSet' => $this->testSet,
            'executionServer' => $this->executionServer
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
     * Set slug
     *
     * @param string $slug
     * @return TestSetRun
     */
    public function setSlug($slug) {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return TestSetRun
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
     * Set testSet
     *
     * @param TestSet $testSet
     * @return TestSetRun
     */
    public function setTestSet(TestSet $testSet = null) {
        $this->testSet = $testSet;

        return $this;
    }

    /**
     * Get testSet
     *
     * @return TestSet
     */
    public function getTestSet() {
        return $this->testSet;
    }

    /**
     * Set status
     *
     * @param Status $status
     * @return TestSetRun
     */
    public function setStatus(Status $status = null) {
        $this->status = $status;
        foreach ($this->testRuns as $testRun) {
            $testRun->setStatus($status);
        }

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
     * Set executionServer
     *
     * @param ExecutionServer $executionServer
     * @return TestSetRun
     */
    public function setExecutionServer(ExecutionServer $executionServer = null) {
        $this->executionServer = $executionServer;

        return $this;
    }

    /**
     * Get executionServer
     *
     * @return ExecutionServer
     */
    public function getExecutionServer() {
        return $this->executionServer;
    }

    /**
     * Add testRuns
     *
     * @param TestRun $testRuns
     * @return TestSetRun
     */
    public function addTestRun(TestRun $testRuns) {
        $testRuns->setTestSetRun($this);
        $this->testRuns[] = $testRuns;

        return $this;
    }

    /**
     * Remove testRuns
     *
     * @param TestRun $testRuns
     */
    public function removeTestRun(TestRun $testRuns) {
        $this->testRuns->removeElement($testRuns);
    }

    /**
     * Get testRuns
     *
     * @return Collection
     */
    public function getTestRuns() {
        return $this->testRuns;
    }

    /**
     * Set gearmanJobHandle
     *
     * @param string $gearmanJobHandle
     * @return TestSetRun
     */
    public function setGearmanJobHandle($gearmanJobHandle) {
        $this->gearmanJobHandle = $gearmanJobHandle;

        return $this;
    }

    /**
     * Get gearmanJobHandle
     *
     * @return string
     */
    public function getGearmanJobHandle() {
        return $this->gearmanJobHandle;
    }

    /**
     * Set user
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     * @return TestSetRun
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set executionReport
     *
     * @param \App\MainBundle\Entity\ExecutionReport $executionReport
     * @return TestSetRun
     */
    public function setExecutionReport(\App\MainBundle\Entity\ExecutionReport $executionReport = null) {
        $this->executionReport = $executionReport;

        return $this;
    }

    /**
     * Get executionReport
     *
     * @return \App\MainBundle\Entity\ExecutionReport
     */
    public function getExecutionReport() {
        return $this->executionReport;
    }

    /**
     * Set endedAt
     *
     * @param \DateTime $endedAt
     * @return TestSetRun
     */
    public function setEndedAt($endedAt) {
        $this->endedAt = $endedAt;

        return $this;
    }

    /**
     * Get endedAt
     *
     * @return \DateTime
     */
    public function getEndedAt() {
        return $this->endedAt;
    }

    /**
     * Set startedAt
     *
     * @param \DateTime $startedAt
     * @return TestSetRun
     */
    public function setStartedAt($startedAt) {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime
     */
    public function getStartedAt() {
        return $this->startedAt;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return TestSetRun
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

}
