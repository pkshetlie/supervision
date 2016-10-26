<?php
/**
 * Created by PhpStorm.
 * User: pierr
 * Date: 21/10/2016
 * Time: 23:03
 */

namespace App\CoreBundle\Entity;

use App\ProjectBundle\Entity\Project;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Project[]
     *
     * @ORM\OneToMany(targetEntity="App\ProjectBundle\Entity\Project",mappedBy="user")
     */
    private $projects;

    public function __construct()
    {
        parent::__construct();
        $this->projects = new ArrayCollection();

    }

    /**
     * Add project
     *
     * @param \App\ProjectBundle\Entity\Project $project
     *
     * @return User
     */
    public function addProject(\App\ProjectBundle\Entity\Project $project)
    {
        $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param \App\ProjectBundle\Entity\Project $project
     */
    public function removeProject(\App\ProjectBundle\Entity\Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    public function getMd5Email(){
        return md5($this->email);
    }
}
