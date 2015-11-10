<?php

namespace App\MainBundle\Entity;

use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="business_step", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_Unique", columns={"name", "business_step_folder_id"})})
 * @UniqueEntity(
 *      fields={"name", "businessStepFolder"},
 *      message="Name already used.",
 *      groups="business_step"
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class BusinessStep extends StepContainer implements JsonSerializable {

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
     * @ORM\ManyToOne(targetEntity="BusinessStepFolder", inversedBy="businessSteps")
     * @ORM\JoinColumn(name="business_step_folder_id", referencedColumnName="id", nullable=true)
     */
    protected $businessStepFolder;

    /**
     * @ORM\OneToMany(targetEntity="Step", mappedBy="businessStep", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $steps;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="businessStepsStartingWith")
     * @ORM\JoinColumn(name="starting_page_id", referencedColumnName="id", nullable=true)
     */
    protected $startingPage;

    /**
     * @ORM\ManyToOne(targetEntity="Application")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    /**
     * @ORM\OneToOne(targetEntity="ParameterSet", inversedBy="businessStep", cascade={"all"})
     * @ORM\JoinColumn(name="parameter_set_id", referencedColumnName="id")
     */
    protected $parameterSet;

    /**
     * @ORM\OneToOne(targetEntity="StepSentenceGroup", inversedBy="businessStep", cascade={"all"})
     * @ORM\JoinColumn(name="step_sentence_group_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $stepSentenceGroup;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->steps = new ArrayCollection();
    }

    public function __toString() {
        $result = "";
        if ($this->businessStepFolder != null) {
            $result .= $this->businessStepFolder . "\\";
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
            'businessStepFolder' => $this->businessStepFolder,
            'parameterSet' => $this->parameterSet
        );
    }

    public function getJsonTreeAsArray() {
        $result = array(
            'text' => $this->name . ' <span class="pull-right"><small>' . $this->description . '</small></span>',
            'icon' => "fontello-icon-level-up",
            'href' => '#node-business-step-' . $this->id,
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

    public function getParentName() {
        return $this->businessStepFolder->getParentName();
    }

    public function getFolder() {
        return $this->getBusinessStepFolder();
    }

    public function setFolder($folder) {
        return $this->setBusinessStepFolder($folder);
    }

    public function generateSentence() {
        $sentence = $this->name;
        foreach ($this->parameterSet->getParameters() as $parameter) {
            $sentence .= " %" . $parameter->getPlaceholder() . "%";
        }
        return $sentence;
    }

    public function isLastStepHasControlSteps() {
        $result = false;
        $lastStep = $this->steps->get($this->steps->count() - 1);
        if ($lastStep !== null) {
            $result = $lastStep->getControlSteps()->count() > 0;
        }
        return $result;
    }

    public function updateParameterSet(LifecycleEventArgs $event) {
        $em = $event->getEntityManager();
        $slugify = new Slugify();
        foreach ($this->getSteps() as $step) {
            $this->refreshParameterSetFromParameterDatas($em, $slugify, $step->getParameterDatas());
            foreach ($step->getControlSteps() as $controlStep) {
                $this->refreshParameterSetFromParameterDatas($em, $slugify, $controlStep->getParameterDatas());
            }
        }
    }

    private function refreshParameterSetFromParameterDatas($em, $slugify, $parameterDatas) {
        foreach ($parameterDatas as $parameterData) {
            $value = $parameterData->getValue();
            if (substr($value, 0, 1) === "/") {
                $name = substr($value, 1);
                $parameter = $em->getRepository("AppMainBundle:Parameter")
                        ->findByNameInParameterSet($name, $this->parameterSet);
                if ($parameter === null) {
                    $parameter = new Parameter();
                    $parameter->setName($name);
                    $parameter->setPlaceholder($slugify->slugify($name));
                    $this->parameterSet->addParameter($parameter);
                }
            }
        }
        $em->persist($this->parameterSet);
        $em->flush();
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
     * @return BusinessStep
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
     * @return BusinessStep
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
     * @return BusinessStep
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
     * Set businessStepFolder
     *
     * @param BusinessStepFolder $businessStepFolder
     * @return BusinessStep
     */
    public function setBusinessStepFolder(BusinessStepFolder $businessStepFolder = null) {
        $this->businessStepFolder = $businessStepFolder;

        return $this;
    }

    /**
     * Get businessStepFolder
     *
     * @return BusinessStepFolder
     */
    public function getBusinessStepFolder() {
        return $this->businessStepFolder;
    }

    /**
     * Add steps
     *
     * @param Step $steps
     * @return BusinessStep
     */
    public function addStep(Step $steps) {
        $steps->setBusinessStep($this);
        $steps->setOrder($this->getSteps()->count() + 1);
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
        $result = new ArrayCollection();
        foreach ($this->steps as $step) {
            if ($step->getTest() === null) {
                $result->add($step);
            }
        }
        return $result;
    }

    /**
     * Set startingPage
     *
     * @param Page $startingPage
     * @return BusinessStep
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

    /**
     * Set application
     *
     * @param Application $application
     * @return BusinessStep
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

    /**
     * Set parameterSet
     *
     * @param ParameterSet $parameterSet
     * @return BusinessStep
     */
    public function setParameterSet(ParameterSet $parameterSet = null) {
        $this->parameterSet = $parameterSet;

        return $this;
    }

    /**
     * Get parameterSet
     *
     * @return ParameterSet
     */
    public function getParameterSet() {
        return $this->parameterSet;
    }

    /**
     * Set stepSentenceGroup
     *
     * @param \App\MainBundle\Entity\StepSentenceGroup $stepSentenceGroup
     * @return BusinessStep
     */
    public function setStepSentenceGroup(\App\MainBundle\Entity\StepSentenceGroup $stepSentenceGroup = null) {
        $this->stepSentenceGroup = $stepSentenceGroup;

        return $this;
    }

    /**
     * Get stepSentenceGroup
     *
     * @return \App\MainBundle\Entity\StepSentenceGroup
     */
    public function getStepSentenceGroup() {
        return $this->stepSentenceGroup;
    }

}
