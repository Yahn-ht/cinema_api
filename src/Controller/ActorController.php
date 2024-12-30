<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Repository\ActorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActorController extends AbstractController
{
    #[Route('/api/actors', name: 'app_actors',methods:['GET'])]
    public function getAllActors(Request $request,ActorRepository $actorRepository,SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get("page",1);
        $limit = $request->get("limit",3);
        $actors = $actorRepository->findAllPerPage($page,$limit);

        
        if(!$actors)
            return new JsonResponse(null,Response::HTTP_NOT_FOUND,[],true);
        $actorsJson = $serializer->serialize($actors,'json',['groups' =>  "getActors"]);

        return new JsonResponse($actorsJson,Response::HTTP_OK,[],true);
    }

    #[Route('/api/actors/{id}',name:'app_actor_detail',methods:["GET"])]
    public function getActor(Actor $actor,SerializerInterface $serializer)
    {
        
        if(!$actor)
            return new JsonResponse(null,Response::HTTP_NOT_FOUND,[],true);
        $actorJson = $serializer->serialize($actor,'json',[]);
        return new JsonResponse($actorJson,Response::HTTP_OK,[],true);
    }


    #[Route('/api/actors/{id}',name:'app_delete_actor',methods:["DELETE"])]
    public function deleteActor(Actor $actor,EntityManagerInterface $em)
    {
        $em->remove($actor);
        $em->flush();
        return new JsonResponse(null,Response::HTTP_OK,[]);
    }



    #[Route('/api/actors/{id}', name: 'updateActor', methods: ["PUT"])]
    public function updateBook(Actor $actor,Request $request,EntityManagerInterface $em,SerializerInterface $serializer,ValidatorInterface $validator){

        $serializer->deserialize($request->getContent(),Actor::class,'json',[AbstractObjectNormalizer::OBJECT_TO_POPULATE => $actor]);

        $errors = $validator->validate($actor);
        
        $photoFile = $request->files->get('picture');
        if ($photoFile) {
            // Supprimez l'ancienne photo si elle existe
            if ($actor->getPicture()) {
                $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public' . $actor->getPicture();
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            // Vérifiez si le fichier est une image valide
            if (!in_array($photoFile->getMimeType(), ['image/jpeg', 'image/png'])) {
                return new JsonResponse(
                    ['error' => 'Only JPEG and PNG files are allowed.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            // Enregistrez la nouvelle photo
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/actors';
            $photoFileName = uniqid() . '.' . $photoFile->guessExtension();
            $photoFile->move($uploadsDir, $photoFileName);

            $actor->setPicture('/uploads/actors/' . $photoFileName);
        }

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->persist($actor);

        $em->flush();

        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }



    #[Route('/api/actor/create',name:'createActor',methods:['POST'])]
    public function createActor(Request $request,EntityManagerInterface $em,SerializerInterface $serializer,ValidatorInterface $validator)
    {

        $actor = new Actor();
        $name = $request->request->get('name');
        $actor->setName($name);
        $errors = $validator->validate($actor);
    
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(["errors" => $errorMessages],JsonResponse::HTTP_BAD_REQUEST);
        }

        $photoFile = $request->files->get('picture');
        if ($photoFile) {
            // Vérifiez si le fichier est une image valide
            if (!in_array($photoFile->getMimeType(), ['image/jpeg', 'image/png'])) {
                return new JsonResponse(
                    ['error' => 'Only JPEG and PNG files are allowed.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            // Déplacez le fichier dans le répertoire de stockage
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/actors';
            $photoFileName = uniqid() . '.' . $photoFile->guessExtension();
            $photoFile->move($uploadsDir, $photoFileName);

            // Définir le chemin de la photo dans l'entité
            $actor->setPicture('/uploads/actors/' . $photoFileName);
        }

        $em->persist($actor);
        $em->flush();

        return new JsonResponse($serializer->serialize($actor,'json',[]),Response::HTTP_CREATED,[],true);
    }
}
