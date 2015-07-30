<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="test", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name", "test_folder_id"})})
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used.",
 *      groups="test"
 * )
 * @UniqueEntity(
 *      fields={"testFolder"},
 *      groups="test"
 * )
 */
class Test implements JsonSerializable {

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
     * @ORM\ManyToOne(targetEntity="TestFolder", inversedBy="tests")
     * @ORM\JoinColumn(name="test_folder_id", referencedColumnName="id", nullable=true)
     */
    protected $testFolder;

    /**
     * @ORM\OneToMany(targetEntity="Step", mappedBy="test", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $steps;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="testsStartingWith")
     * @ORM\JoinColumn(name="starting_page_id", referencedColumnName="id", nullable=true)
     */
    protected $startingPage;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->steps = new ArrayCollection();
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
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'testFolder' => $this->testFolder
        );
    }

    public function getJsonTreeAsArray() {
        $result = array(
            'text' => $this->name . ' <span class="pull-right"><small>' . $this->description . '</small></span>',
            'icon' => "fontello-icon-tasks",
            'href' => '#node-test-' . $this->id,
            'state' => array(
                'selected' => $this->selected
        ));
        return $result;
    }

    public function getPageAtStepOrder($order) {
        foreach ($this->steps as $step) {
            if ($step->getOrder() === $order) {
                break;
            }
            $previousStep = $step;
        }
        $page = null;
        if ($previousStep !== null) {
            $page = $previousStep->getActivePage();
        }
        if ($page === null) {
            $page = $this->startingPage;
        }
        return $page;
    }

    public function getActivePage() {
        return $this->getPageAtStepOrder($this->steps->count());
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
     * @return Test
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
     * @return Test
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
     * @return Test
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
     * Set testFolder
     *
     * @param \App\MainBundle\Entity\TestFolder $testFolder
     * @return Test
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
     * Add steps
     *
     * @param \App\MainBundle\Entity\Step $steps
     * @return Test
     */
    public function addStep(\App\MainBundle\Entity\Step $steps) {
        $steps->setTest($this);
        $steps->setOrder($this->steps->count() + 1);
        $this->steps[] = $steps;

        return $this;
    }

    /**
     * Remove steps
     *
     * @param \App\MainBundle\Entity\Step $steps
     */
    public function removeStep(\App\MainBundle\Entity\Step $steps) {
        $this->steps->removeElement($steps);
    }

    /**
     * Get steps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSteps() {
        return $this->steps;
    }

    /**
     * Set startingPage
     *
     * @param \App\MainBundle\Entity\Page $startingPage
     * @return Test
     */
    public function setStartingPage(\App\MainBundle\Entity\Page $startingPage = null) {
        $this->startingPage = $startingPage;

        return $this;
    }

    /**
     * Get startingPage
     *
     * @return \App\MainBundle\Entity\Page
     */
    public function getStartingPage() {
        return $this->startingPage;
    }

}
