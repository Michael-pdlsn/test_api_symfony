<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'user_roles')]
class UserRole
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ALLOWED_METHODS_ADMIN = ['PUT', 'POST', 'GET', 'DELETE'];
    const ROLE_USER = 'ROLE_USER';
    const ALLOWED_METHODS_USER = ['PUT', 'POST', 'GET'];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $name;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'role')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, unique: true)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
