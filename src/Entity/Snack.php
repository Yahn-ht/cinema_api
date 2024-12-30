<?php

namespace App\Entity;

use App\Repository\SnackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SnackRepository::class)]
class Snack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getSnacks",'getReservations'])]
    private ?int $id = null;

    #[Groups(["getSnacks",'getReservations'])]
    #[ORM\Column(type: 'decimal')]
    #[Assert\NotBlank(message:"le prix est requis")]
    private ?string $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message:"la photo est requise")]
    #[Groups(["getSnacks",'getReservations'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"le nom est requis")]
    #[Groups(["getSnacks",'getReservations'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'snacks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getSnacks",'getReservations'])]
    private ?CategorySnack $category = null;

    /**
     * @var Collection<int, SnackReservation>
     */
    #[ORM\OneToMany(targetEntity: SnackReservation::class, mappedBy: 'snack')]
    private Collection $snackReservations;

    public function __construct()
    {
        $this->snackReservations = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
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

    public function getCategory(): ?CategorySnack
    {
        return $this->category;
    }

    public function setCategory(?CategorySnack $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, SnackReservation>
     */
    public function getSnackReservations(): Collection
    {
        return $this->snackReservations;
    }

    public function addSnackReservation(SnackReservation $snackReservation): static
    {
        if (!$this->snackReservations->contains($snackReservation)) {
            $this->snackReservations->add($snackReservation);
            $snackReservation->setSnack($this);
        }

        return $this;
    }

    public function removeSnackReservation(SnackReservation $snackReservation): static
    {
        if ($this->snackReservations->removeElement($snackReservation)) {
            // set the owning side to null (unless already changed)
            if ($snackReservation->getSnack() === $this) {
                $snackReservation->setSnack(null);
            }
        }

        return $this;
    }

}
