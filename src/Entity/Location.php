<?php

namespace App\Entity;

use App\Entity\Trait\IdTrait;
use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    use IdTrait;

    public function __construct(
        #[ORM\Column(length: 255)]
        private string $city,

        #[ORM\Column(length: 255, nullable: true)]
        private ?string $region = null,

        #[ORM\Column(length: 255, nullable: true)]
        private ?string $country = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }
}
