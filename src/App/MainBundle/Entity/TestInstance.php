<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="test_instance")
 */
class TestInstance implements JsonSerializable {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="_order", type="integer")
     */
    protected $order;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="last_runned_at", type="datetime", nullable=true)
     */
    protected $lastRunnedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Test")
     * @ORM\JoinColumn(name="test_id", referencedColumnName="id")
     */
    protected $test;

    /**
     * @ORM\ManyToOne(targetEntity="TestSet", inversedBy="testInstances")
     * @ORM\JoinColumn(name="test_set_id", referencedColumnName="id")
     */
    protected $testSet;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    protected $status;

    public function __construct() {
        $this->createdAt = new DateTime();
    }

    public function __toString() {
        $result = $this->order;
        if ($this->test != null) {
            $result .= " - " . $this->test . "\\";
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'order' => $this->order,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'lastRunnedAt' => $this->lastRunnedAt->format('d/m/Y H:i:s')
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
     * Set order
     *
     * @param integer $order
     * @return TestInstance
     */
    public function setOrder($order) {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return TestInstance
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
     * Set lastRunnedAt
     *
     * @param \DateTime $lastRunnedAt
     * @return TestInstance
     */
    public function setLastRunnedAt($lastRunnedAt) {
        $this->lastRunnedAt = $lastRunnedAt;

        return $this;
    }

    /**
     * Get lastRunnedAt
     *
     * @return \DateTime
     */
    public function getLastRunnedAt() {
        return $this->lastRunnedAt;
    }

    /**
     * Set test
     *
     * @param \App\MainBundle\Entity\Test $test
     * @return TestInstance
     */
    public function setTest(\App\MainBundle\Entity\Test $test = null) {
        $this->test = $test;

        return $this;
    }

    /**
     * Get test
     *
     * @return \App\MainBundle\Entity\Test
     */
    public function getTest() {
        return $this->test;
    }

    /**
     * Set testSet
     *
     * @param \App\MainBundle\Entity\TestSet $testSet
     * @return TestInstance
     */
    public function setTestSet(\App\MainBundle\Entity\TestSet $testSet = null) {
        $this->testSet = $testSet;

        return $this;
    }

    /**
     * Get testSet
     *
     * @return \App\MainBundle\Entity\TestSet
     */
    public function getTestSet() {
        return $this->testSet;
    }

    /**
     * Set status
     *
     * @param \App\MainBundle\Entity\Status $status
     * @return TestInstance
     */
    public function setStatus(\App\MainBundle\Entity\Status $status = null) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \App\MainBundle\Entity\Status
     */
    public function getStatus() {
        return $this->status;
    }

}