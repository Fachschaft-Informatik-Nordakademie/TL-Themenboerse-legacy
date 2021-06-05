<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

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
    private string $email;

    #[ORM\Column(type: "string", length: 255, nullable: true, unique: true)]
    private ?string $ldapUsername;

    #[ORM\Column(type: "string", length: 500, nullable: true, unique: true)]
    private ?string $ldapDn;

    #[ORM\Column(type: "string", name: '`password`', nullable: true)]
    private ?string $password;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: "App\Entity\Topic", mappedBy: "author")]
    private PersistentCollection $topics;

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

    public function getSalt(): ?string
    {
        // Returning a salt is only needed, if you are not using a modern
        // hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
        return null;
    }

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

    /**
     * Get the value of topics
     *
     * @return  ?PersistentCollection
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * Set the value of topics
     *
     * @param  ?PersistentCollection  $topics
     *
     * @return  self
     */
    public function setTopics(?PersistentCollection $topics)
    {
        $this->topics = $topics;

        return $this;
    }
}
