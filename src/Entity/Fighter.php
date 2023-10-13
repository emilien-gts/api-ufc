<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Fighter
{
    use UuidTrait;

    #[ORM\Column(type: Types::STRING, nullable: false)]
    #[Assert\Length(exactly: 16)]
    private string $token;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $fullName = null;

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
