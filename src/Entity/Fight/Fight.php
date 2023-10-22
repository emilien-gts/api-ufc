<?php

namespace App\Entity\Fight;

use App\Entity\Event;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TokenTrait;
use App\Enum\Fight\Method;
use App\Repository\FightRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FightRepository::class)]
class Fight
{
    use IdTrait;
    use TokenTrait;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'fights')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: Referee::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Referee $referee = null;

    #[ORM\OneToMany(mappedBy: 'fight', targetEntity: FighterFight::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(exactly: 2)]
    private Collection $fighters;

    #[ORM\Column(type: Types::STRING, enumType: Method::class)]
    #[Assert\NotBlank]
    private ?Method $method = null;

    public function __construct()
    {
        $this->fighters = new ArrayCollection();
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): Fight
    {
        $this->event = $event;

        return $this;
    }

    public function getReferee(): ?Referee
    {
        return $this->referee;
    }

    public function setReferee(?Referee $referee): Fight
    {
        $this->referee = $referee;

        return $this;
    }

    public function getMethod(): ?Method
    {
        return $this->method;
    }

    public function setMethod(?Method $method): Fight
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return Collection<int, FighterFight>
     */
    public function getFighters(): Collection
    {
        return $this->fighters;
    }

    public function addFighter(FighterFight $fighter): static
    {
        if (!$this->fighters->contains($fighter)) {
            $this->fighters->add($fighter);
            $fighter->setFight($this);
        }

        return $this;
    }

    public function removeFighter(FighterFight $fighter): static
    {
        if ($this->fighters->removeElement($fighter)) {
            // set the owning side to null (unless already changed)
            if ($fighter->getFight() === $this) {
                $fighter->setFight(null);
            }
        }

        return $this;
    }
}
