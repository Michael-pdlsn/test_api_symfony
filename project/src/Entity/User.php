<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Сутність користувача складається з 4 атрибутів
 * $id, $login, $phone, $pass.
 * На всіх атрибутах повинні бути правила валідації:
 * довжина - не більше 8 символів.
 * Також закрита можливість дублікату за атрибутами $login, $pass
 * валідацією: повісити на них унікальний складовий індекс.
 */

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['login', 'pass'], message: 'The combination of login and pass must be unique.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 8)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    private ?string $login = null;

    #[ORM\Column(type: 'string', length: 8)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    private ?string $pass = null;

    #[ORM\Column(type: 'string', length: 8)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    private ?string $phone = null;

    #[ORM\OneToOne(targetEntity: UserRole::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserRole $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): self
    {
        $this->pass = $pass;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getRoles(): array
    {
        return [$this->role->getName() ?? ''];
    }

    public function eraseCredentials(): void
    {
        return;
    }

    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    public function getPassword(): ?string
    {
        return $this->pass;
    }
}
