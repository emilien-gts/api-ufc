<?php

namespace App\Entity;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TokenTrait;
use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use IdTrait;
    use TokenTrait;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?bool $isPpv = null;

    #[ORM\Column]
    private ?bool $isUltimeFighter = null;

    #[ORM\ManyToOne(targetEntity: Location::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isPpv(): ?bool
    {
        return $this->isPpv;
    }

    public function setIsPpv(bool $isPpv): static
    {
        $this->isPpv = $isPpv;

        return $this;
    }

    public function isUltimeFighter(): ?bool
    {
        return $this->isUltimeFighter;
    }

    public function setIsUltimeFighter(?bool $isUltimeFighter): void
    {
        $this->isUltimeFighter = $isUltimeFighter;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }
}
