<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "`user`")]
class User implements UserInterface, EquatableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "string", name: '`type`', length: 50, nullable: false)]
    private string $type;

    #[ORM\Column(type: "string", length: 255, nullable: false, unique: true)]
    #[Email(message: 'The e-mail address is not valid.')]
    private string $email;

    #[ORM\Column(type: "string", length: 255, nullable: true, unique: true)]
    private ?string $ldapUsername;

    #[ORM\Column(type: "string", length: 500, nullable: true, unique: true)]
    private ?string $ldapDn;

    #[ORM\Column(type: "string", name: '`password`', nullable: true)]
    #[Ignore]
    private ?string $password;

    #[ORM\OneToMany(targetEntity: Topic::class, mappedBy: "author")]
    #[Ignore]
    private PersistentCollection $topics;

    #[ORM\OneToOne(targetEntity: UserProfile::class, mappedBy: "user", fetch: 'EAGER', cascade: ['all'])]
    #[Assert\Valid]
    private UserProfile $profile;

    public function getRoles(): array
    {
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    #[Ignore]
    public function getSalt(): ?string
    {
        // Returning a salt is only needed, if you are not using a modern
        // hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
        return null;
    }

    #[Ignore]
    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLdapUsername(): ?string
    {
        return $this->ldapUsername;
    }

    public function setLdapUsername(string $ldapUsername): self
    {
        $this->ldapUsername = $ldapUsername;

        return $this;
    }

    public function getLdapDn(): ?string
    {
        return $this->ldapDn;
    }

    public function setLdapDn(string $ldapDn): self
    {
        $this->ldapDn = $ldapDn;

        return $this;
    }

    // we can not use default user comparison because some users don't have a password
    // see https://symfony.com/doc/current/security/user_provider.html#understanding-how-users-are-refreshed-from-the-session
    #[Ignore]
    public function isEqualTo(UserInterface $user): bool
    {

        if (!($user instanceof User)) {
            return false;
        }

        if ($this->type !== $user->type) {
            return false;
        }

        if ($this->type === UserType::EXTERNAL && $this->password !== $user->password) {
            return false;
        }

        if ($this->type === UserType::LDAP && $this->ldapUsername !== $user->ldapUsername) {
            return false;
        }

        if ($this->type === UserType::LDAP && $this->ldapDn !== $user->ldapDn) {
            return false;
        }

        return true;
    }

    public function getTopics(): ?PersistentCollection
    {
        return $this->topics;
    }

    public function setTopics(?PersistentCollection $topics): self
    {
        $this->topics = $topics;

        return $this;
    }

    public function getProfile(): UserProfile
    {
        return $this->profile;
    }

    public function setProfile(UserProfile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }


}
