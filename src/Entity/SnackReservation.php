<?php

namespace App\Entity;

use App\Repository\SnackReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SnackReservationRepository::class)]
class SnackReservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getReservations'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'snackReservations')]
    #[Groups(['getReservations'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Snack $snack = null;

    #[ORM\ManyToOne(inversedBy: 'snackReservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\Column]
    #[Groups(['getReservations'])]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSnack(): ?Snack
    {
        return $this->snack;
    }

    public function setSnack(?Snack $snack): static
    {
        $this->snack = $snack;

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
