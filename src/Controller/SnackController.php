<?php

namespace App\Controller;

use App\Entity\CategorySnack;
use App\Entity\Snack;
use App\Repository\SnackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SnackController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/api/snacks', name: 'app_get_snacks', methods: ['GET'])]
    public function getAllSnacks(SnackRepository $snackRepository, SerializerInterface $serializer): JsonResponse
    {
        $snacks = $snackRepository->findAll();
        $snacksJson = $serializer->serialize($snacks, 'json',["groups"=>"getSnacks"]);
        
        return new JsonResponse($snacksJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/snacks/{id}', name: 'app_get_snack', methods: ['GET'])]
    public function getSnack(Snack $snack, SerializerInterface $serializer): JsonResponse
    {
        $snackJson = $serializer->serialize($snack, 'json',["groups"=>"getSnacks"]);
        return new JsonResponse($snackJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/snack/create', name: 'app_create_snack', methods: ['POST'])]
    public function createSnack(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {


        // Récupération des données form-data
        $name = $request->request->get('name');
        $prix = $request->request->get('prix');
        $categoryId = $request->request->get('category');
        $photoFile = $request->files->get('picture');
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Le nom du film est requis.';
        }

        if (empty($prix)) {
            $errors[] = 'La durée est requise.';
        }

        if (empty($categoryId)) {
            $errors[] = 'La description est requise.';
        }

        if (empty($photoFile)) {
            $errors[] = 'Le nom de l\'auteur est requis.';
        }


        // Si des erreurs existent, retourner un message d'erreur
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors], 400);
        }

        $categorieSnack = $this->em->getRepository(CategorySnack::class)->find($categoryId);
            if (!$categorieSnack) {
                return new JsonResponse(['error' => "CategoryMovie with ID ".$categoryId." not found."], JsonResponse::HTTP_NOT_FOUND);
            }

        $snack = new Snack();
        $snack->setName($name);
        $snack->setPrix($prix);
        $snack->setCategory($categorieSnack);

        if ($photoFile) {
            // Vérifiez si le fichier est une image valide
            if (!in_array($photoFile->getMimeType(), ['image/jpeg', 'image/png'])) {
                return new JsonResponse(
                    ['error' => 'Only JPEG and PNG files are allowed.'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            // Déplacez le fichier dans le répertoire de stockage
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/snacks';
            $photoFileName = uniqid() . '.' . $photoFile->guessExtension();
            $photoFile->move($uploadsDir, $photoFileName);

            // Définir le chemin de la photo dans l'entité
            $snack->setPicture('/uploads/snacks/' . $photoFileName);
        }

        $errors = $validator->validate($snack);
        
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(["errors" => $errorMessages],JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $this->em->persist($snack);
        $this->em->flush();

        return new JsonResponse($serializer->serialize($snack, 'json',['groups' => "getSnacks"]), Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/snacks/{id}', name: 'app_update_snack', methods: ['PUT'])]
    public function updateSnack(Snack $snack, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Snack::class, 'json', [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $snack]);
        
        $photoFile = $request->files->get('picture');
        if ($photoFile) {
            // Supprimez l'ancienne photo si elle existe
            if ($snack->getPicture()) {
                $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public' . $snack->getPicture();
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
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/snacks';
            $photoFileName = uniqid() . '.' . $photoFile->guessExtension();
            $photoFile->move($uploadsDir, $photoFileName);

            $snack->setPicture('/uploads/snacks/' . $photoFileName);
        }

        $errors = $validator->validate($snack);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(["errors" => $errorMessages],JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/snacks/{id}', name: 'app_delete_snack', methods: ['DELETE'])]
    public function deleteSnack(Snack $snack): JsonResponse
    {
        $this->em->remove($snack);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
