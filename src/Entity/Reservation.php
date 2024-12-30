<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getSnacks",'getReservations'])]
    private ?int $id = null;

    #[ORM\Column(type:'decimal')]
    #[Groups(["getSnacks",'getReservations'])]
    private ?string $montant = null;

    /**
     * @var Collection<int, Place>
     */
    #[Groups(['getReservations'])]
    #[ORM\OneToMany(targetEntity: Place::class, mappedBy: 'reservation')]
    private Collection $places;


    #[ORM\ManyToOne(inversedBy: 'reservation')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getReservations'])]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE,options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups(['getReservations'])]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, SnackReservation>
     */
    
    #[ORM\OneToMany(targetEntity: SnackReservation::class, mappedBy: 'reservation')]
    #[Groups(['getReservations'])]
    private Collection $snackReservations;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[Groups(['getReservations'])]
    private ?Movie $movieReserve = null;

    public function __construct()
    {
        $this->places = new ArrayCollection();
        $this->snackReservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

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
            $place->setIsConcerne($this);
        }

        return $this;
    }

    public function removePlace(Place $place): static
    {
        if ($this->places->removeElement($place)) {
            // set the owning side to null (unless already changed)
            if ($place->getIsConcerne() === $this) {
                $place->setIsConcerne(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, SnackReservation>
     */
    public function getSnackReservations(): Collection
    {
        return $this->snackReservations;
    }

    public function calculateTotalPrice(): void
    {
        $total = 0;

        // Ajout du coût des places
        foreach ($this->places as $place) {
            $total += $place->getCategory()->getPrice();
        }

        // Ajout du coût des snacks (quantité * prix)
        foreach ($this->snackReservations as $snack) {
            $total += $snack->getSnack()->getPrix() * $snack->getQuantity();
        }

        // Mise à jour du montant total
        $this->montant = (string) $total;
    }


    public function addSnackReservation(SnackReservation $snackReservation): static
    {
        if (!$this->snackReservations->contains($snackReservation)) {
            $this->snackReservations->add($snackReservation);
            $snackReservation->setReservation($this);
        }

        return $this;
    }

    public function removeSnackReservation(SnackReservation $snackReservation): static
    {
        if ($this->snackReservations->removeElement($snackReservation)) {
            // set the owning side to null (unless already changed)
            if ($snackReservation->getReservation() === $this) {
                $snackReservation->setReservation(null);
            }
        }

        return $this;
    }

    public function getMovieReserve(): ?Movie
    {
        return $this->movieReserve;
    }

    public function setMovieReserve(?Movie $movieReserve): static
    {
        $this->movieReserve = $movieReserve;

        return $this;
    }
}
