<?php

namespace App\Entity;

use App\Repository\CategorySnackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorySnackRepository::class)]
class CategorySnack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getSnacks",'getReservations'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Le nom est requis")]
    #[Groups(["getSnacks",'getReservations'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Snack>
     */
    #[ORM\OneToMany(targetEntity: Snack::class, mappedBy: 'category')]
    private Collection $snacks;

    public function __construct()
    {
        $this->snacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Snack>
     */
    public function getSnacks(): Collection
    {
        return $this->snacks;
    }

    public function addSnack(Snack $snack): static
    {
        if (!$this->snacks->contains($snack)) {
            $this->snacks->add($snack);
            $snack->setCategory($this);
        }

        return $this;
    }

    public function removeSnack(Snack $snack): static
    {
        if ($this->snacks->removeElement($snack)) {
            // set the owning side to null (unless already changed)
            if ($snack->getCategory() === $this) {
                $snack->setCategory(null);
            }
        }

        return $this;
    }
}
