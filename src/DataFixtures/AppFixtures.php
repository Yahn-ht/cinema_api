<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\CategoryMovie;
use App\Entity\CategoryPlace;
use App\Entity\CategorySnack;
use App\Entity\Movie;
use App\Entity\MovieActor;
use App\Entity\Place;
use App\Entity\Reservation;
use App\Entity\Salle;
use App\Entity\Session;
use App\Entity\Snack;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordEncoder;
    private $connection;

    public function __construct(UserPasswordHasherInterface $passwordEncoder,EntityManagerInterface $em)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->connection = $em->getConnection();
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Création des catégories
        $categoryMovies = ['Horreur','Comedie','Animé'];
        $categoryPlaces = [
            ['name' => 'VIP', 'price' => 100.0],
            ['name' => 'STANDARD', 'price' => 60.0]
        ];
        $categorySnacks = ['PopCorn','Boisson','Chips'];

        // Ajout des catégories de films
        foreach ($categoryMovies as $name) {
            $categoryMovie = new CategoryMovie();
            $categoryMovie->setName($name);
            $manager->persist($categoryMovie);
        }

        // Ajout des catégories de places
        foreach ($categoryPlaces as $category) {
            $categoryPlace = new CategoryPlace();
            $categoryPlace->setName($category['name']);
            $categoryPlace->setPrice($category['price']);
            $manager->persist($categoryPlace);
        }

        // Ajout des catégories de snacks
        foreach ($categorySnacks as $name) {
            $categorySnack = new CategorySnack();
            $categorySnack->setName($name);
            $manager->persist($categorySnack);
        }

//         // Création des acteurs
//         $actors = [];
//         for ($i = 0; $i < 5; $i++) {
//             $actor = new Actor();
//             $actor->setName($faker->name)
//                     ->setPicture($faker->imageUrl(640, 480, 'people', true));
//             $manager->persist($actor);
//             $actors[] = $actor;
//         }

// // Création des films et association avec des catégories
// $movies = [];
// for ($i = 0; $i < 3; $i++) {
//     $movie = new Movie();
//     $movie->setName($faker->sentence(3))
//         ->setDuree(new \DateTime('02:00:00'))
//         ->setDescription($faker->paragraph)
//         ->setImage($faker->imageUrl(640, 480, 'movies', true))
//         ->setAuthorName($faker->name)
//         ->setSupp(false);
//     $manager->persist($movie); // Persistez les films ici
//     $movies[] = $movie;
// }

// $manager->flush();  // Assurez-vous que les films sont persistés avant d'essayer d'associer des acteurs

// // Association des acteurs aux films via MovieActor
// foreach ($movies as $movie) {
//     // Sélection de 2 acteurs au hasard pour chaque film
//     $randomActors = array_rand($actors, 2); // Choisir 2 acteurs au hasard
//     foreach ((array)$randomActors as $actorIndex) {
//         // Ajouter une entrée dans la table movie_actor sans utiliser d'entité MovieActor
//         $this->connection->insert('movie_actor', [
//             'movie_id' => $movie->getId(), // Assurez-vous que l'id du film est défini
//             'actor_id' => $actors[$actorIndex]->getId(),
//         ]);
//     }
// }


//         // Création des salles
//         $salles = [];
//         for ($i = 0; $i < 2; $i++) {
//             $salle = new Salle();
//             $salle->setNumero($faker->randomDigitNotNull)
//                     ->setNbrePlace($faker->numberBetween(50, 200))
//                     ->setSupp(false);
//             $manager->persist($salle);
//             $salles[] = $salle;
//         }

//         // Création des sessions de films
//         $sessions = [];
//         foreach ($movies as $movie) {
//             $session = new Session();
//             $session->setMovie($movie)
//                     ->setDate($faker->dateTimeThisMonth)
//                     ->setSupp(false)
//                     ->setSalle($salles[array_rand($salles)]);
//             $manager->persist($session);
//             $sessions[] = $session;
//         }

//         // Création des places
//         $places = [];
//         foreach ($categoryPlaces as $category) {
//             for ($i = 0; $i < 10; $i++) {
//                 $place = new Place();
//                 $place->setCategory($category)
//                         ->setSalle($salles[array_rand($salles)])
//                         ->setNumero($i + 1)
//                         ->setReserve(false)
//                         ->setSupp(false);
//                 $manager->persist($place);
//                 $places[] = $place;
//             }
//         }

//         // Création des snacks
//         $snacks = [];
//         foreach ($categorySnacks as $category) {
//             for ($i = 0; $i < 3; $i++) {
//                 $snack = new Snack();
//                 $snack->setCategory($category)
//                         ->setName($faker->word)
//                         ->setPrix($faker->randomFloat(2, 1, 10))
//                         ->setPicture($faker->imageUrl(640, 480, 'food', true))
//                         ->setQuantity($faker->randomNumber(2));
//                 $manager->persist($snack);
//                 $snacks[] = $snack;
//             }
//         }

//         // Création des réservations
//         $reservations = [];
//         for ($i = 0; $i < 3; $i++) {
//             $reservation = new Reservation();
//             $reservation->setMontant($faker->randomFloat(2, 10, 100));
//             $manager->persist($reservation);
//             $reservations[] = $reservation;
//         }

        // Création des utilisateurs
        $users = [];
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setEmail($faker->email)
                    ->setRoles(['ROLE_USER'])
                    ->setPassword($this->passwordEncoder->hashPassword($user, '123456'))
                    ->setName($faker->name)
                    ->setSupp(false)
                    ->setCodeVerification($faker->randomNumber(6));
            $manager->persist($user);
            $users[] = $user;
        }

        // Création d'un administrateur
        $admin = new User();
        $admin->setEmail('admin@example.com')
                ->setRoles(['ROLE_ADMIN'])
                ->setPassword($this->passwordEncoder->hashPassword($admin, '123456'))
                ->setName('Admin User')
                ->setSupp(false)
                ->setCodeVerification($faker->randomNumber(6));
        $manager->persist($admin);

        // Persister toutes les données
        $manager->flush();
    }
}
