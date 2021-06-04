<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\TopicRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TopicRepository::class)]
#[ORM\Table(name: "`topic`")]
class Topic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "string", name: '`type`', length: 500, nullable: false)]
    private string $title;

    #[ORM\Column(type: "string", length: 4000, nullable: true)]
    private string $description;

    #[ORM\Column(type: "string", length: 1000, nullable: true)]
    private ?string $requirements;

    /*Hier vllt auch eine Array von Strings?*/
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $website;

    #[ORM\Column(type: "simple_array", nullable: true)]
    private ?array $tags;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $scope;


    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTime $start;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $pages;


    #[ORM\Column(type: "string", name: '`type`', length: 50, nullable: false)]
    private string $status;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTime $deadline;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "topics")]
    private ?User $author;

    /**
     * Get the value of id
     *
     * @return  ?int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of title
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param  string  $title
     *
     * @return  self
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of description
     *
     * @return  string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param  string  $description
     *
     * @return  self
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of requirements
     *
     * @return  ?string
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Set the value of requirements
     *
     * @param  ?string  $requirements
     *
     * @return  self
     */
    public function setRequirements(?string $requirements)
    {
        $this->requirements = $requirements;

        return $this;
    }

    /**
     * Get the value of website
     *
     * @return  ?string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set the value of website
     *
     * @param  ?string  $website
     *
     * @return  self
     */
    public function setWebsite(?string $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get the value of tags
     *
     * @return  array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set the value of tags
     *
     * @param  array  $tags
     *
     * @return  self
     */
    public function setTags(?array $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get the value of scope
     *
     * @return  ?string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set the value of scope
     *
     * @param  ?string  $scope
     *
     * @return  self
     */
    public function setScope(?string $scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get the value of deadline
     *
     * @return  ?\DateTime
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Set the value of deadline
     *
     * @param  ?\DateTime  $deadline
     *
     * @return  self
     */
    public function setDeadline(?\DateTime $deadline)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Get the value of author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set the value of author
     *
     * @return  self
     */
    public function setAuthor(?User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of start
     *
     * @return  ?\DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set the value of start
     *
     * @param  ?\DateTime  $start
     *
     * @return  self
     */
    public function setStart(?\DateTime $start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get the value of pages
     *
     * @return  ?int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set the value of pages
     *
     * @param  ?int  $pages
     *
     * @return  self
     */
    public function setPages(?int $pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * Get the value of status
     *
     * @return  string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param  string  $status
     *
     * @return  self
     */
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }
}
