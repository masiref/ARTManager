<?php

namespace App\MainBundle\Entity;

use App\MainBundle\Services\GherkinService;
use App\MainBundle\Services\MinkService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

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
        $this->createdAt = new DateTime();
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
        $page = $this->startingPage;
        if ($order > 0) {
            $step = null;
            foreach ($this->steps as $step) {
                if ($step->getOrder() === $order) {
                    break;
                }
            }
            if ($step !== null) {
                $page = $step->getActivePage();
            }
        }
        return $page;
    }

    public function getActivePage() {
        return $this->getPageAtStepOrder($this->steps->count());
    }

    public function getBehatScenario(GherkinService $gherkin) {
        return $gherkin->generateBehatScenario($this);
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
     * @param DateTime $createdAt
     * @return Test
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
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
     * @param TestFolder $testFolder
     * @return Test
     */
    public function setTestFolder(TestFolder $testFolder = null) {
        $this->testFolder = $testFolder;

        return $this;
    }

    /**
     * Get testFolder
     *
     * @return TestFolder
     */
    public function getTestFolder() {
        return $this->testFolder;
    }

    /**
     * Add steps
     *
     * @param Step $steps
     * @return Test
     */
    public function addStep(Step $steps) {
        $steps->setTest($this);
        $steps->setOrder($this->steps->count() + 1);
        $this->steps[] = $steps;

        return $this;
    }

    /**
     * Remove steps
     *
     * @param Step $steps
     */
    public function removeStep(Step $steps) {
        $this->steps->removeElement($steps);
    }

    /**
     * Get steps
     *
     * @return Collection
     */
    public function getSteps() {
        return $this->steps;
    }

    /**
     * Set startingPage
     *
     * @param Page $startingPage
     * @return Test
     */
    public function setStartingPage(Page $startingPage = null) {
        $this->startingPage = $startingPage;

        return $this;
    }

    /**
     * Get startingPage
     *
     * @return Page
     */
    public function getStartingPage() {
        return $this->startingPage;
    }

}
