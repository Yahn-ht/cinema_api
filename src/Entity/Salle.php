<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['numero'], message: 'Le numéro "{{ value }}" est déjà utilisé.')]
#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPlaces","getMovies","getSessions","getSalles"])]
    private ?int $id = null;

    #[ORM\Column(type:'integer',unique: true)]
    #[Groups(["getPlaces","getMovies","getSessions","getSalles"])]
    #[Assert\NotBlank(message:"Le numero est requis")]
    #[Assert\Positive(message: "Le numéro doit être un entier positif.")]
    private ?int $numero = null;

    #[ORM\Column]
    #[Groups(["getPlaces","getMovies","getSessions","getSalles"])]
    #[Assert\NotBlank(message:"Le nombre de place est requis")]
    #[Assert\GreaterThan(0, message: "Le nombre de places doit être supérieur à zéro.")]
    private ?int $nbrePlace = null;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'salle')]
    #[Groups(["getSalles"])]
    private Collection $sessions;

    /**
     * @var Collection<int, Place>
     */
    #[ORM\OneToMany(targetEntity: Place::class, mappedBy: 'salle', orphanRemoval: true)]
    #[Groups(["getSalles"])]
    private Collection $places;

    #[ORM\Column]
    #[Groups(["getMovies","getSessions","getSalles"])]
    private ?bool $isSupp = false;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->places = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getNbrePlace(): ?int
    {
        return $this->nbrePlace;
    }

    public function setNbrePlace(int $nbrePlace): static
    {
        $this->nbrePlace = $nbrePlace;

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setSalle($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getSalle() === $this) {
                $session->setSalle(null);
            }
        }

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
            $place->setSalle($this);
        }

        return $this;
    }

    public function removePlace(Place $place): static
    {
        if ($this->places->removeElement($place)) {
            // set the owning side to null (unless already changed)
            if ($place->getSalle() === $this) {
                $place->setSalle(null);
            }
        }

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
}
