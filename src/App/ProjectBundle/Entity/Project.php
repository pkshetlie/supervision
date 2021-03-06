<?php

namespace App\ProjectBundle\Entity;

use App\CoreBundle\Entity\EntityCore;
use App\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="App\ProjectBundle\Repository\ProjectRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Project extends EntityCore
{
    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", length=255, nullable=true)
     */
    protected $apiKey;

    /**
     * @var string
     *
     * @ORM\Column(name="api_token", type="string", length=255, nullable=true)
     */
    protected $apiToken;



    /**
     * @var Error[]
     *
     * @ORM\OneToMany(targetEntity="App\ProjectBundle\Entity\Error",mappedBy="project")
     */
    protected $errors;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Project
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return Project
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set apiToken
     *
     * @param string $apiToken
     *
     * @return Project
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * Get apiToken
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->errors = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add error
     *
     * @param \App\ProjectBundle\Entity\Error $error
     *
     * @return Project
     */
    public function addError(\App\ProjectBundle\Entity\Error $error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Remove error
     *
     * @param \App\ProjectBundle\Entity\Error $error
     */
    public function removeError(\App\ProjectBundle\Entity\Error $error)
    {
        $this->errors->removeElement($error);
    }

    /**
     * Get errors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
