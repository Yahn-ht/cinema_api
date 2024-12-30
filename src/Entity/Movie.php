<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getMovies","getActors","getSessions","getSalles","getReservations"])]
    private ?int $id = null;

    #[Groups(["getMovies","getActors","getSessions","getSalles","getReservations"])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(["getMovies","getActors","getSessions"])]
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $duree = null;

    #[Groups(["getMovies","getActors","getSessions"])]
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[Groups(["getMovies","getActors","getSessions","getReservations"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Groups(["getMovies","getActors","getSessions"])]
    #[ORM\Column(length: 255)]
    private ?string $authorName = null;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'movie', orphanRemoval: true)]
    #[Groups(["getMovies"])]
    private Collection $session;

    #[ORM\Column]
    #[Groups(["getMovies","getActors","getSessions"])]
    private ?bool $isSupp = false;

    /**
     * @var Collection<int, Actor>
     */
    #[ORM\ManyToMany(targetEntity: Actor::class, inversedBy: 'movies')]
    #[Groups(["getMovies","getSessions"])]
    private Collection $actors;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    #[Groups(["getMovies"])]
    private ?CategoryMovie $categorieMovie = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favories')]
    #[Groups(["getMovies"])]
    private Collection $users;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'movieReserve')]
    private Collection $reservations;

    public function __construct()
    {
        $this->session = new ArrayCollection();
        $this->actors = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->reservations = new ArrayCollection();
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

    public function getDuree(): ?\DateTimeInterface
    {
        return $this->duree;
    }

    public function setDuree(\DateTimeInterface $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): static
    {
        $this->authorName = $authorName;

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSession(): Collection
    {
        return $this->session;
    }

    public function addSession(Session $session): static
    {
        if (!$this->session->contains($session)) {
            $this->session->add($session);
            $session->setMovie($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->session->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getMovie() === $this) {
                $session->setMovie(null);
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

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
        }

        return $this;
    }

    public function removeActor(Actor $actor): static
    {
        $this->actors->removeElement($actor);

        return $this;
    }

    public function getCategorieMovie(): ?CategoryMovie
    {
        return $this->categorieMovie;
    }

    public function setCategorieMovie(?CategoryMovie $categorieMovie): static
    {
        $this->categorieMovie = $categorieMovie;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFavory($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeFavory($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setMovieReserve($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getMovieReserve() === $this) {
                $reservation->setMovieReserve(null);
            }
        }

        return $this;
    }
}
