<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getMovies","getSessions","getSalles","getPlaces"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getMovies","getSessions","getSalles"])]
    #[Assert\NotBlank(message:"Le date est requise")]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'session')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getSessions","getSalles"])]
    #[Assert\NotBlank(message:"Le film est requis")]
    private ?Movie $movie = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[Groups(["getMovies","getSessions"])]
    #[Assert\NotBlank(message:"La salle est requise")]
    private ?Salle $salle = null;

    #[ORM\Column]
    #[Groups(["getMovies","getSessions","getSalles"])]
    private ?bool $isSupp = false;

    /**
     * @var Collection<int, Place>
     */
    #[ORM\ManyToMany(targetEntity: Place::class, mappedBy: 'reserver')]
    private Collection $places;

    public function __construct()
    {
        $this->places = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): static
    {
        $this->movie = $movie;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    public function isSupp(): ?bool
    {
        return $this->isSupp;
    }

    public function setSupp(bool $isSupp): static
    {
        $this->isSupp = $isSupp;

        return $this;
    }

    /**
     * @return Collection<int, Place>
     */
    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): static
    {
        if (!$this->places->contains($place)) {
            $this->places->add($place);
            $place->addReserver($this);
        }

        return $this;
    }

    public function removePlace(Place $place): static
    {
        if ($this->places->removeElement($place)) {
            $place->removeReserver($this);
        }

        return $this;
    }
}
