<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="step_sentence")
 */
class StepSentence implements JsonSerializable {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotNull
     */
    protected $sentence;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotNull
     */
    protected $locale;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="StepSentenceGroup", mappedBy="sentences")
     */
    protected $groups;

    public function __construct() {
        $this->createdAt = new DateTime();
        $this->groups = new ArrayCollection();
    }

    public function __toString() {
        if ($this->sentence != null && $this->sentence != "") {
            return $this->sentence;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'sentence' => $this->sentence,
            'locale' => $this->locale,
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
     * Set sentence
     *
     * @param string $sentence
     * @return StepSentence
     */
    public function setSentence($sentence) {
        $this->sentence = $sentence;

        return $this;
    }

    /**
     * Get sentence
     *
     * @return string
     */
    public function getSentence() {
        return $this->sentence;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return StepSentence
     */
    public function setLocale($locale) {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return StepSentence
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
     * Add groups
     *
     * @param \App\MainBundle\Entity\StepSentenceGroup $groups
     * @return StepSentence
     */
    public function addGroup(\App\MainBundle\Entity\StepSentenceGroup $groups) {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \App\MainBundle\Entity\StepSentenceGroup $groups
     */
    public function removeGroup(\App\MainBundle\Entity\StepSentenceGroup $groups) {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups() {
        return $this->groups;
    }

}
