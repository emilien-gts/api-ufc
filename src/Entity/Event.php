<?php

namespace App\Entity;

use App\Entity\Fight\Fight;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TokenTrait;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use IdTrait;
    use TokenTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Assert\NotNull]
    private ?bool $isPpv = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Assert\NotNull]
    private ?bool $isUltimeFighter = null;

    #[ORM\ManyToOne(targetEntity: Location::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Fight::class, orphanRemoval: true)]
    private Collection $fights;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->fights = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Fight>
     */
    public function getFights(): Collection
    {
        return $this->fights;
    }

    public function addFight(Fight $fight): static
    {
        if (!$this->fights->contains($fight)) {
            $this->fights->add($fight);
            $fight->setEvent($this);
        }

        return $this;
    }

    public function removeFight(Fight $fight): static
    {
        if ($this->fights->removeElement($fight)) {
            // set the owning side to null (unless already changed)
            if ($fight->getEvent() === $this) {
                $fight->setEvent(null);
            }
        }

        return $this;
    }
}
