<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="test_set", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name", "test_set_folder_id"})})
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="test_set"
 * )
 * @UniqueEntity(
 *      fields={"testSetFolder"},
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

    public function __construct() {
        $this->createdAt = new DateTime();
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
            'status' => $this->status
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
     * @param \App\MainBundle\Entity\TestSetFolder $testSetFolder
     * @return TestSet
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
     * Set application
     *
     * @param \App\MainBundle\Entity\Application $application
     * @return TestSet
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

    function getSelected() {
        return $this->selected;
    }

    function setSelected($selected) {
        $this->selected = $selected;
    }

    /**
     * Set status
     *
     * @param \App\MainBundle\Entity\Status $status
     * @return TestSet
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
