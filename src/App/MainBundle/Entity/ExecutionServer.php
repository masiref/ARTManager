<?php

namespace App\MainBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="execution_server")
 * @UniqueEntity(
 *      fields={"name"},
 *      message="Name already used."
 * )
 */
class ExecutionServer implements JsonSerializable {

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
    protected $artRunnerPath;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="executionServers")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id")
     */
    protected $server;

    public function __construct() {
        $this->createdAt = new DateTime();
    }

    public function __toString() {
        if ($this->name != null && $this->name != "") {
            return $this->name;
        }
        return "New";
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'artRunnerPath' => $this->artRunnerPath,
            'createdAt' => $this->createdAt->format('d/m/Y H:i:s'),
            'server' => $this->server
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
     * @return ExecutionServer
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
     * @return ExecutionServer
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
     * Set artRunnerPath
     *
     * @param string $artRunnerPath
     * @return ExecutionServer
     */
    public function setArtRunnerPath($artRunnerPath) {
        if (substr($artRunnerPath, -1) !== DIRECTORY_SEPARATOR) {
            $artRunnerPath .= DIRECTORY_SEPARATOR;
        }
        $this->artRunnerPath = $artRunnerPath;

        return $this;
    }

    /**
     * Get artRunnerPath
     *
     * @return string
     */
    public function getArtRunnerPath() {
        return $this->artRunnerPath;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ExecutionServer
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
     * Set server
     *
     * @param \App\MainBundle\Entity\Server $server
     * @return ExecutionServer
     */
    public function setServer(\App\MainBundle\Entity\Server $server = null) {
        $this->server = $server;

        return $this;
    }

    /**
     * Get server
     *
     * @return \App\MainBundle\Entity\Server
     */
    public function getServer() {
        return $this->server;
    }

}
