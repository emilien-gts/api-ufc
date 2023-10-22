<?php

namespace App\Entity\Fight;

use App\Entity\Trait\IdTrait;
use App\Repository\RefereeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefereeRepository::class)]
class Referee
{
    use IdTrait;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
        private string $fullName
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }
}
