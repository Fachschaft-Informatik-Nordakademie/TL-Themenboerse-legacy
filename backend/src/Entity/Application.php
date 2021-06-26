<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\ApplicationRepository;
use Symfony\Component\Serializer\Annotation\Ignore;
use Doctrine\ORM\Mapping as ORM;

class ApplicationStatus
{
    const OPEN = "OPEN";
    const ACCEPTED = "ACCEPTED";
    const REJECTED = "REJECTED";
}

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(name: "`application`")]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "string", length: 1000, nullable: false)]
    private string $content;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $candidate;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Topic::class)]
    private Topic $topic;

    #[ORM\Column(type: "string", length: 10, nullable: false)]
    private string $status;

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

    /**
     * Get the value of topic
     *
     * @return  Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set the value of topic
     *
     * @param  Topic  $topic
     *
     * @return  self
     */
    public function setTopic(Topic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get the value of candidate
     *
     * @return  User
     */
    public function getCandidate()
    {
        return $this->candidate;
    }

    /**
     * Set the value of candidate
     *
     * @param  User  $candidate
     *
     * @return  self
     */
    public function setCandidate(User $candidate)
    {
        $this->candidate = $candidate;

        return $this;
    }

    /**
     * Get the value of content
     *
     * @return  string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @param  string  $content
     *
     * @return  self
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of id
     *
     * @return  ?int
     */
    public function getId()
    {
        return $this->id;
    }
}
