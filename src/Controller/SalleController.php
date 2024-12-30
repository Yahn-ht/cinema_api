<?php

namespace App\Controller;

use App\Entity\CategoryPlace;
use App\Entity\Place;
use App\Entity\Salle;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SalleController extends AbstractController
{

    #[Route('/api/salle/create', name: 'createSalle', methods: ['POST'])]
    public function create(Request $request,EntityManagerInterface $em,SerializerInterface $serializer,ValidatorInterface $validator,SalleRepository $salleRepository): JsonResponse
    {
        $data = json_decode($request->getContent(),true);
        

        if (!isset($data['nbreStandard']) || !isset($data['nbreVip']) || !isset($data['nbrePlace'])) {
            return new JsonResponse(["errors" => "Les informations nécessaires sont manquantes."], JsonResponse::HTTP_BAD_REQUEST);
        }

        if($data['nbrePlace'] != ($data['nbreVip']+$data['nbreStandard'])){
            return new JsonResponse(["errors" => "La somme nombre de place vip et du nombre de place standard doit etre egale au nombre total de place"],JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $salle = new Salle();
        
        $salle->setNumero($data['numero']);
        $salle->setNbrePlace($data['nbrePlace']);

        $errors = $validator->validate($salle);
    
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(["errors" => $errorMessages],JsonResponse::HTTP_BAD_REQUEST);
        }

        // $em->persist($salle);
        // $em->flush();
        //$id = $salle->getId();
        $standard = $em->getRepository(CategoryPlace::class)->find(2);
        $vip = $em->getRepository(CategoryPlace::class)->find(1);

        for($i = 1; $i <= $data['nbreStandard'];$i++){
            $place = new Place();
            $place->setNumero($i);
            $place->setCategory($standard);
            $salle->addPlace($place);
            $em->persist($place);
        }

        for($i = $data['nbreStandard']+1; $i <= $data['nbreVip'] + $data['nbreStandard'];$i++){
            $place = new Place();
            $place->setNumero($i);
            $place->setCategory($vip);
            $salle->addPlace($place);
            $em->persist($place);
        }

        $em->persist($salle);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($salle, 'json',['groups'=>'getSalles']),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/api/salles/{id}', name: 'getSalle', methods: ['GET'])]
    public function show(Salle $salle,EntityManagerInterface $em,
    SerializerInterface $serializer,
    ValidatorInterface $validator): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize($salle, 'json',['groups'=>'getSalles']),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/salles/{id}', name: 'updateSalle', methods: ['PUT'])]
    public function update(EntityManagerInterface $em,SerializerInterface $serializer,ValidatorInterface $validator,Salle $salle, Request $request): JsonResponse
    {
        $serializer->deserialize(
            $request->getContent(),
            Salle::class,
            'json',
            [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $salle]
        );

        $errors = $validator->validate($salle);
        if (count($errors) > 0) {
            return new JsonResponse(
                $serializer->serialize($errors, 'json',['groups'=>'getSalles']),
                JsonResponse::HTTP_BAD_REQUEST,
                [],
                true
            );
        }

        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/salles/{id}', name: 'deleteSalle', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em,
    SerializerInterface $serializer,
    ValidatorInterface $validator,Salle $salle): JsonResponse
    {
        $em->remove($salle);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/salles', name: 'listSalles', methods: ['GET'])]
    public function index(SerializerInterface $serializer,ValidatorInterface $validator,SalleRepository $salleRepository): JsonResponse
    {
        $salles = $salleRepository->findAll();

        return new JsonResponse(
            $serializer->serialize($salles, 'json',['groups'=>'getSalles']),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
