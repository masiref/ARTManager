<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(name="business_step_folder", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="IDX_Unique_Application", columns={"name", "application_id"}),
 *      @ORM\UniqueConstraint(name="IDX_Unique_Business_Step_Folder", columns={"name", "parent_id"})
 * })
 * @UniqueEntity(
 *      fields={"name", "application"},
 *      message="Name already used.",
 *      groups="business_step_folder_application"
 * )
 * @UniqueEntity(
 *      fields={"name", "businessStepFolder"},
 *      message="Name already used.",
 *      groups="business_step_folder_business_step_folder"
 * )
 */
class BusinessStepFolder implements JsonSerializable {

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
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="businessStepFolders")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     */
    protected $application;

    /**
     * @ORM\ManyToOne(targetEntity="BusinessStepFolder", inversedBy="businessStepFolders")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $businessStepFolder;

    /**
     * @ORM\OneToMany(targetEntity="BusinessStepFolder", mappedBy="businessStepFolder", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $businessStepFolders;

    /**
     * @ORM\OneToMany(targetEntity="BusinessStep", mappedBy="businessStepFolder", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $businessSteps;

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->businessStepFolders = new ArrayCollection();
        $this->businessSteps = new ArrayCollection();
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
            'createdAt' => $this->createdAt->format('d/m/Y \a\t H:i:s'),
            'application' => $this->application
        );
    }

    public function getBusinessStepFoldersCount() {
        $count = $this->businessStepFolders->count();
        foreach ($this->businessStepFolders as $businessStepFolder) {
            $count += $businessStepFolder->getBusinessStepFoldersCount();
        }
        return $count;
    }

    public function getBusinessStepsCount() {
        $count = $this->getBusinessSteps()->count();
        foreach ($this->businessStepFolders as $businessStepFolder) {
            $count += $businessStepFolder->getBusinessStepsCount();
        }
        return $count;
    }

    public function getJsonTreeAsArray() {
        $result = array(
            'text' => $this->name . ' <span class="pull-right"><small>' . $this->description . '</small></span>',
            'icon' => 'fontello-icon-folder',
            'href' => '#node-folder-' . $this->id,
            'state' => array(
                'selected' => $this->selected
        ));
        if ($this->businessStepFolders->count() > 0 || $this->businessSteps->count() > 0) {
            $result["nodes"] = $this->getNodes();
        }
        return $result;
    }

    private function getNodes() {
        $nodes = array();
        foreach ($this->businessStepFolders as $businessStepFolder) {
            $nodes[] = $businessStepFolder->getJsonTreeAsArray();
        }
        foreach ($this->businessSteps as $businessStep) {
            $nodes[] = $businessStep->getJsonTreeAsArray();
        }
        return $nodes;
    }

    public function getRootApplication() {
        $application = $this->application;
        $parent = $this->businessStepFolder;
        while ($application == null && $parent != null) {
            $application = $parent->getApplication();
            $parent = $parent->getBusinessStepFolder();
        }
        return $application;
    }

    public function getParentName() {
        if ($this->businessStepFolder === null) {
            return $this->name;
        }
        return $this->businessStepFolder->getParentName() . " > " . $this->name;
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
     * @return BusinessStepFolder
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
     * @return BusinessStepFolder
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
     * @return BusinessStepFolder
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
     * Set application
     *
     * @param \App\MainBundle\Entity\Application $application
     * @return BusinessStepFolder
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

    /**
     * Set businessStepFolder
     *
     * @param \App\MainBundle\Entity\BusinessStepFolder $businessStepFolder
     * @return BusinessStepFolder
     */
    public function setBusinessStepFolder(\App\MainBundle\Entity\BusinessStepFolder $businessStepFolder = null) {
        $this->businessStepFolder = $businessStepFolder;

        return $this;
    }

    /**
     * Get businessStepFolder
     *
     * @return \App\MainBundle\Entity\BusinessStepFolder
     */
    public function getBusinessStepFolder() {
        return $this->businessStepFolder;
    }

    /**
     * Add businessStepFolders
     *
     * @param \App\MainBundle\Entity\BusinessStepFolder $businessStepFolders
     * @return BusinessStepFolder
     */
    public function addBusinessStepFolder(\App\MainBundle\Entity\BusinessStepFolder $businessStepFolders) {
        $this->businessStepFolder = $this;
        $this->businessStepFolders[] = $businessStepFolders;

        return $this;
    }

    /**
     * Remove businessStepFolders
     *
     * @param \App\MainBundle\Entity\BusinessStepFolder $businessStepFolders
     */
    public function removeBusinessStepFolder(\App\MainBundle\Entity\BusinessStepFolder $businessStepFolders) {
        $this->businessStepFolders->removeElement($businessStepFolders);
    }

    /**
     * Get businessStepFolders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBusinessStepFolders() {
        return $this->businessStepFolders;
    }

    /**
     * Add businessSteps
     *
     * @param \App\MainBundle\Entity\BusinessStep $businessSteps
     * @return BusinessStepFolder
     */
    public function addBusinessStep(\App\MainBundle\Entity\BusinessStep $businessSteps) {
        $businessSteps->setBusinessStepFolder($this);
        $businessSteps->setApplication($this->getRootApplication());
        $this->businessSteps[] = $businessSteps;

        return $this;
    }

    /**
     * Remove businessSteps
     *
     * @param \App\MainBundle\Entity\BusinessStep $businessSteps
     */
    public function removeBusinessStep(\App\MainBundle\Entity\BusinessStep $businessSteps) {
        $this->businessSteps->removeElement($businessSteps);
    }

    /**
     * Get businessSteps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBusinessSteps() {
        return $this->businessSteps;
    }

}
