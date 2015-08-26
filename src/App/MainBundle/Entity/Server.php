<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="server")
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used."
 * )
 */
class Server implements JsonSerializable {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
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
    protected $description;

    /**
     * @ORM\Column(type="string")
     */
    protected $host;

    /**
     * @ORM\Column(type="integer")
     */
    protected $port;

    /**
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="ExecutionServer", mappedBy="server", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $executionServers;

    public function __construct() {
        $this->createdAt = new DateTime();
    }

    public function __toString() {
        if ($this->name != null && $this->name != "") {
            return $this->name . " on " . $this->host;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s')
        );
    }

    public function getXEditableNode() {
        return array(
            'value' => $this->id,
            'text' => $this->name . " on " . $this->host
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
     * Set name
     *
     * @param string $name
     * @return Server
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
     * @return Server
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
     * Set host
     *
     * @param string $host
     * @return Server
     */
    public function setHost($host) {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Set port
     *
     * @param integer $port
     * @return Server
     */
    public function setPort($port) {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return integer
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Server
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Server
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Server
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
     * Add executionServers
     *
     * @param \App\MainBundle\Entity\ExecutionServer $executionServers
     * @return Server
     */
    public function addExecutionServer(\App\MainBundle\Entity\ExecutionServer $executionServers) {
        $executionServers->setServer($this);
        $this->executionServers[] = $executionServers;

        return $this;
    }

    /**
     * Remove executionServers
     *
     * @param \App\MainBundle\Entity\ExecutionServer $executionServers
     */
    public function removeExecutionServer(\App\MainBundle\Entity\ExecutionServer $executionServers) {
        $this->executionServers->removeElement($executionServers);
    }

    /**
     * Get executionServers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExecutionServers() {
        return $this->executionServers;
    }

}
