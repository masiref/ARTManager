<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="execution_report")
 */
class ExecutionReport implements JsonSerializable {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $report;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    public function __construct() {
        $this->createdAt = new DateTime();
    }

    public function __toString() {
        if ($this->id != null) {
            return $this->id;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'report' => $this->report,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s')
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
     * Set report
     *
     * @param string $report
     * @return ExecutionReport
     */
    public function setReport($report) {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return string
     */
    public function getReport() {
        return $this->report;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ExecutionReport
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

}
