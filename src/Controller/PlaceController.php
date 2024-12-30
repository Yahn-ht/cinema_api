<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlaceController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/api/places', name: 'app_get_places', methods: ['GET'])]
    public function getAllPlaces(PlaceRepository $placeRepository, SerializerInterface $serializer): JsonResponse
    {
        $places = $placeRepository->findAll();
        $placesJson = $serializer->serialize($places, 'json',['groups' =>"getPlaces"]);
        
        return new JsonResponse($placesJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/places/{id}', name: 'app_get_place', methods: ['GET'])]
    public function getPlace(Place $place, SerializerInterface $serializer): JsonResponse
    {
        $placeJson = $serializer->serialize($place, 'json',['groups' =>"getPlaces"]);
        return new JsonResponse($placeJson, Response::HTTP_OK, [], true);
    }

    // #[Route('/api/place/create', name: 'app_create_place', methods: ['POST'])]
    // public function createPlace(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    // {
    //     $place = $serializer->deserialize($request->getContent(), Place::class, 'json');

    //     $errors = $validator->validate($place);

    
    //     if (count($errors) > 0) {
    //         $errorMessages = [];
    //         foreach ($errors as $error) {
    //             $errorMessages[$error->getPropertyPath()] = $error->getMessage();
    //         }
    //         return new JsonResponse(["errors" => $errorMessages],JsonResponse::HTTP_BAD_REQUEST);
    //     }
        
    //     $this->em->persist($place);
    //     $this->em->flush();

    //     return new JsonResponse($serializer->serialize($place, 'json'), Response::HTTP_CREATED, [], true);
    // }

    #[Route('/api/places/{id}', name: 'app_update_place', methods: ['PUT'])]
    public function updatePlace(Place $place, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Place::class, 'json', [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $place]);
        
        $errors = $validator->validate($place);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/places/{id}', name: 'app_delete_place', methods: ['DELETE'])]
    public function deletePlace(Place $place): JsonResponse
    {
        $this->em->remove($place);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
