<?php

namespace App\MainBundle\Entity;

use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="test_run")
 */
class TestRun implements JsonSerializable {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="slug", type="string")
     */
    protected $slug;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="TestInstance", inversedBy="runs")
     * @ORM\JoinColumn(name="test_instance_id", referencedColumnName="id")
     */
    protected $testInstance;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="TestSetRun", inversedBy="testRuns")
     * @ORM\JoinColumn(name="test_set_run_id", referencedColumnName="id")
     */
    protected $testSetRun;

    public function __construct() {
        $this->createdAt = DateTime::createFromFormat("U.u", microtime(true));
        $slugify = new Slugify();
        $this->slug = $slugify->slugify($this->id . $this->createdAt->format('d-m-Y H:i:s.u'));
    }

    public function __toString() {
        if ($this->testInstance != null) {
            $result = $this->testInstance . " \\ " . $this->slug;
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
            'testInstance' => $this->testInstance,
            'testSetRun' => $this->testSetRun
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
     * @return TestRun
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
     * @return TestRun
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
     * Set testInstance
     *
     * @param TestInstance $testInstance
     * @return TestRun
     */
    public function setTestInstance(TestInstance $testInstance = null) {
        $this->testInstance = $testInstance;

        return $this;
    }

    /**
     * Get testInstance
     *
     * @return TestInstance
     */
    public function getTestInstance() {
        return $this->testInstance;
    }

    /**
     * Set status
     *
     * @param Status $status
     * @return TestRun
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
     * Set testSetRun
     *
     * @param TestSetRun $testSetRun
     * @return TestRun
     */
    public function setTestSetRun(TestSetRun $testSetRun = null) {
        $this->testSetRun = $testSetRun;

        return $this;
    }

    /**
     * Get testSetRun
     *
     * @return TestSetRun
     */
    public function getTestSetRun() {
        return $this->testSetRun;
    }

}
