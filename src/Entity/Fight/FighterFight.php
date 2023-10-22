<?php

namespace App\Entity\Fight;

use App\Entity\Fighter;
use App\Entity\Trait\IdTrait;
use App\Enum\Fight\Corner;
use App\Repository\Fight\FighterFightRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FighterFightRepository::class)]
class FighterFight
{
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'fighters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fight $fight = null;

    #[ORM\ManyToOne(targetEntity: Fighter::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fighter $fighter = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: Corner::class)]
    private ?Corner $corner = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isWinner = null;

    public function getFight(): ?Fight
    {
        return $this->fight;
    }

    public function setFight(?Fight $fight): static
    {
        $this->fight = $fight;

        return $this;
    }

    public function getFighter(): ?Fighter
    {
        return $this->fighter;
    }

    public function setFighter(?Fighter $fighter): static
    {
        $this->fighter = $fighter;

        return $this;
    }

    public function getCorner(): ?Corner
    {
        return $this->corner;
    }

    public function setCorner(Corner $corner): static
    {
        $this->corner = $corner;

        return $this;
    }

    public function isWinner(): ?bool
    {
        return $this->isWinner;
    }

    public function setIsWinner(bool $isWinner): static
    {
        $this->isWinner = $isWinner;

        return $this;
    }
}
