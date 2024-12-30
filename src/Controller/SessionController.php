<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Salle;
use App\Entity\Session;
use App\Repository\SessionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SessionController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/api/sessions', name: 'app_get_sessions', methods: ['GET'])]
    public function getAllSessions(SessionRepository $sessionRepository, SerializerInterface $serializer): JsonResponse
    {
        $sessions = $sessionRepository->findAll();
        $sessionsJson = $serializer->serialize($sessions, 'json',["groups"=>"getSessions"]);
        
        return new JsonResponse($sessionsJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/sessions/{id}', name: 'app_get_session', methods: ['GET'])]
    public function getSession(Session $session, SerializerInterface $serializer): JsonResponse
    {
        $sessionJson = $serializer->serialize($session, 'json',["groups"=>"getSessions"]);
        return new JsonResponse($sessionJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/session/create', name: 'app_create_session', methods: ['POST'])]
    public function createSession(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(),true);

        if (!isset($data['date'])) {
            return new JsonResponse(["errors" => "La date est manquante."], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!isset($data['salle'])) {
            return new JsonResponse(["errors" => "La salle est  manquante."], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!isset($data['movie'])) {
            return new JsonResponse(["errors" => "Le movie est manquant."], JsonResponse::HTTP_BAD_REQUEST);
        }

        $session = new Session();

        $movie = $this->em->getRepository(Movie::class)->find($data['movie']);
        $salle = $this->em->getRepository(Salle::class)->find($data['salle']);

        if(!$movie){
            return new JsonResponse(["errors" => " Movie not found"], JsonResponse::HTTP_BAD_REQUEST);
        }else{
            $session->setMovie($movie);
        }

        if(!$salle){
            return new JsonResponse(["errors" => " Salle not found"], JsonResponse::HTTP_BAD_REQUEST);
        }else{
            $session->setSalle($salle);
        }

        $session->setDate(new DateTime($data['date']));
        
        $errors = $validator->validate($session);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(["errors" => $errorMessages],JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $this->em->persist($session);
        $this->em->flush();

        return new JsonResponse($serializer->serialize($session, 'json',["groups"=>"getSessions"]), Response::HTTP_CREATED, [], true);
    }

    // #[Route('/api/sessions/{id}', name: 'app_update_session', methods: ['PUT'])]
    // public function updateSession(Session $session, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    // {
    //     $serializer->deserialize($request->getContent(), Session::class, 'json', [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $session]);
        
    //     $errors = $validator->validate($session);
    //     if (count($errors) > 0) {
    //         return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    //     }
        
    //     $this->em->flush();
    //     return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    // }

    #[Route('/api/sessions/{id}', name: 'app_delete_session', methods: ['DELETE'])]
    public function deleteSession(Session $session): JsonResponse
    {
        $this->em->remove($session);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
