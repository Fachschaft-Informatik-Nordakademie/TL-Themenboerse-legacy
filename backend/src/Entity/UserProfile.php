<?php

namespace App\Entity;

use App\Repository\UserProfileRepository;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
#[ORM\Table(name: "`user_profile`")]
class UserProfile
{
    #[ORM\OneToOne(inversedBy: "profile", targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    #[ORM\Id]
    #[Ignore]
    private User $user;

    #[Assert\NotBlank(message: 'The first name must contain at least 2 characters.')]
    #[Assert\Length(min: 2, minMessage: 'The first name must contain at least 2 characters.')]
    #[ORM\Column(type: "string", length: 255, nullable: false)]
    private string $firstName;

    #[Assert\NotBlank(message: 'The last name must contain at least 2 characters.')]
    #[Assert\Length(min: 2, minMessage: 'The last name must contain at least 2 characters.')]
    #[ORM\Column(type: "string", length: 255, nullable: false)]
    private string $lastName;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $image;

    #[ORM\Column(type: "string", length: 2000, nullable: true)]
    private ?string $biography;

    #[ORM\Column(type: "simple_array", nullable: true)]
    private ?array $skills;

    #[ORM\Column(type: "simple_array", name: '`references`', nullable: true)]
    private ?array $references;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): self
    {
        $this->biography = $biography;

        return $this;
    }

    public function getSkills(): ?array
    {
        return $this->skills;
    }

    public function setSkills(?array $skills): self
    {
        $this->skills = $skills;

        return $this;
    }

    public function getReferences(): ?array
    {
        return $this->references;
    }

    public function setReferences(?array $references): self
    {
        $this->references = $references;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
