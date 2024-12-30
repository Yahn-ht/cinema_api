<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPlaces","getSalles",'getReservations'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["getPlaces","getSalles",'getReservations'])]
    #[Assert\NotBlank(message:"Le numero est requis.")]
    private ?int $numero = null;

    #[ORM\ManyToOne(inversedBy: 'places')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getPlaces","getSalles",'getReservations'])]
    #[Assert\NotBlank(message:"La categorie est requise.")]
    private ?CategoryPlace $category = null;

    #[ORM\ManyToOne(inversedBy: 'places')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getPlaces"])]
    private ?Salle $salle = null;

    #[ORM\ManyToOne(inversedBy: 'places')]
    #[Groups(["getPlaces","getSalles"])]
    private ?Reservation $reservation = null;

    #[ORM\Column]
    #[Groups(["getPlaces","getSalles"])]
    private ?bool $isSupp = false;

    /**
     * @var Collection<int, Session>
     */
    #[Groups(["getPlaces","getSalles"])]
    #[ORM\ManyToMany(targetEntity: Session::class, inversedBy: 'places')]
    private Collection $reserver;

    public function __construct()
    {
        $this->reserver = new ArrayCollection();
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

    public function getCategory(): ?CategoryPlace
    {
        return $this->category;
    }

    public function setCategory(?CategoryPlace $category): static
    {
        $this->category = $category;

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

    public function getIsConcerne(): ?Reservation
    {
        return $this->reservation;
    }

    public function setIsConcerne(?Reservation $reservation): static
    {
        $this->reservation = $reservation;

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
     * @return Collection<int, Session>
     */
    public function getReserver(): Collection
    {
        return $this->reserver;
    }

    public function addReserver(Session $reserver): static
    {
        if (!$this->reserver->contains($reserver)) {
            $this->reserver->add($reserver);
        }

        return $this;
    }

    public function removeReserver(Session $reserver): static
    {
        $this->reserver->removeElement($reserver);

        return $this;
    }

}
