<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="prerequisite")
 */
class Prerequisite {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="_order", type="integer")
     */
    protected $order = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Test", inversedBy="prerequisites")
     * @ORM\JoinColumn(name="parent_test_id", referencedColumnName="id")
     */
    protected $parentTest;

    /**
     * @ORM\ManyToOne(targetEntity="Test")
     * @ORM\JoinColumn(name="test_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $test;

    public function __toString() {
        if ($this->test != null) {
            return $this->order . " : " . $this->test;
        }
        return "New";
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
     * @return Prerequisite
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
     * Set test
     *
     * @param Test $test
     * @return Prerequisite
     */
    public function setTest(Test $test = null) {
        $this->test = $test;

        return $this;
    }

    /**
     * Get test
     *
     * @return Test
     */
    public function getTest() {
        return $this->test;
    }

    /**
     * Set parentTest
     *
     * @param \App\MainBundle\Entity\Test $parentTest
     * @return Prerequisite
     */
    public function setParentTest(\App\MainBundle\Entity\Test $parentTest = null) {
        $this->parentTest = $parentTest;

        return $this;
    }

    /**
     * Get parentTest
     *
     * @return \App\MainBundle\Entity\Test
     */
    public function getParentTest() {
        return $this->parentTest;
    }

}
