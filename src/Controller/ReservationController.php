<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\SnackReservation;
use App\Repository\MovieRepository;
use App\Repository\PlaceRepository;
use App\Repository\ReservationRepository;
use App\Repository\SessionRepository;
use App\Repository\SnackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ReservationController extends AbstractController
{
    #[Route('/api/reservation/create', name: 'create_reservation',methods:['POST'])]
    public function reserve(Request $request,EntityManagerInterface $entityManager,PlaceRepository $placeRepository,SnackRepository $snackRepository,SerializerInterface $serializer,MovieRepository $movieRepository,SessionRepository $sessionRepository)
    {
        $data = json_decode($request->getContent(),true);
        $placeIds = $data['places']; // Un tableau des IDs des places
        $snackData = $data['snacks']; // Un tableau des snacks et des quantités [id => quantity]
        $movieId = $data['movie'];
        $sessionId = $data['session'];
        //dd($snackData);
        $errors = [];
        
        if (empty($placeIds)) {
            $errors[] = 'La place est requise.';
        }
        if (empty($sessionId)) {
            $errors[] = 'La session est requise.';
        }
            // if (empty($movieId)) {
            //     $errors[] = 'Le film est requis.';
            // }


        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors], 400);
        }
        $session = $sessionRepository->find($sessionId);
        if(!$session){
            return new JsonResponse(["errors" => "Session not found",Response::HTTP_BAD_REQUEST]);
        }

        $reservation = new Reservation();
        $reservation->setUser($this->getUser());

        foreach ($placeIds as $placeId) {
            // On récupère la place à partir de l'ID
            $place = $placeRepository->find($placeId);
            
            // Vérifie si la place existe
            if (!$place) {
                return new JsonResponse(["errors" => "Place not found"], Response::HTTP_BAD_REQUEST);
            }
        
            // Convertir la collection de sessions réservées en tableau d'IDs
            $reservedSessions = $place->getReserver(); // C'est une PersistentCollection d'objets Session
            $reservedSessionIds = array_map(function ($session) {
                return $session->getId(); // Récupère l'ID de la session
            }, $reservedSessions->toArray()); // Convertir la collection en tableau d'IDs
        
            // Vérifier si la session est déjà associée à la place
            if (in_array($session->getId(), $reservedSessionIds)) {
                return new JsonResponse(["errors" => "Place already reserved"], Response::HTTP_BAD_REQUEST);
            }
        
            // Ajouter la place à la réservation si elle n'est pas déjà réservée
            $reservation->addPlace($place);
        }
        

        // 4. Ajouter les snacks et quantités
        if(!empty($snackData)){
            foreach ($snackData as $snackId => $quantity) {
                $snack = $snackRepository->find($snackId);
                if(!$snack ){
                    return new JsonResponse(["errors" => "Snack not found",Response::HTTP_BAD_REQUEST]);
                }
                if($quantity < 0){
                    return new JsonResponse(["errors" => "quantity must be positive",Response::HTTP_BAD_REQUEST]);
                }
    
                if ($snack && $quantity > 0) {
                    $snackReservation = new SnackReservation();
                    $snackReservation->setSnack($snack);
                    $snackReservation->setQuantity($quantity);
                    $snackReservation->setReservation($reservation);
                    $entityManager->persist($snackReservation);
                    $reservation->addSnackReservation($snackReservation);
                }
            }
        }
        
        $movie = $movieRepository->find($movieId);
        if(!$movie){
            return new JsonResponse(["errors" => "Movie not found",Response::HTTP_BAD_REQUEST]);
        }


        $reservation->setMovieReserve($movie);
        
        $reservation->calculateTotalPrice();

        foreach ($placeIds as $placeId) {
            $place = $placeRepository->find($placeId);
            $place->addReserver($session);
        }

        
        $reservation->setCreatedAt(new \DateTime());

        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($reservation,'json',['groups' => 'getReservations']),JsonResponse::HTTP_CREATED, [], true);
    }

    #[Route('/api/reservations', name: 'getReservtions', methods: ['GET'])]
    public function getAllSnacks(ReservationRepository $reservationRepository, SerializerInterface $serializer): JsonResponse
    {
        $reservation = $reservationRepository->findAll();
        $reservationsJson = $serializer->serialize($reservation, 'json',["groups"=>"getReservations"]);
        
        return new JsonResponse($reservationsJson, Response::HTTP_OK, [], true);
    }


    #[Route('/api/reservations/users', name: 'getUsersReservtions', methods: ['GET'])]
    public function getUsersReservtions(ReservationRepository $reservationRepository, SerializerInterface $serializer): JsonResponse
    {
        /**@var User */
        $user = $this->getUser();
        $reservations = $reservationRepository->findByUser($user);
        $reservationsJson = $serializer->serialize($reservations, 'json',["groups"=>"getReservations"]);
        
        return new JsonResponse($reservationsJson, Response::HTTP_OK, [], true);
    }

}
