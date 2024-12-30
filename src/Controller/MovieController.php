<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Movie;
use App\Repository\CategoryMovieRepository;
use App\Entity\CategoryMovie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MovieController extends AbstractController
{
        #[Route('/api/movie/create', name: 'app_create_movie', methods: ['POST'])]
        public function createMovie(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
        {
            // Récupération des données form-data
            $name = $request->request->get('name');
            $duree = $request->request->get('duree');
            $description = $request->request->get('description');
            $authorName = $request->request->get('authorName');
            $categorieMovieId = $request->request->get('categorieMovie');
            $actorsIdsJson = $request->request->get('actors'); // Cela renvoie la chaîne "[4,5]"
        
            $errors = [];

            if (empty($name)) {
                $errors[] = 'Le nom du film est requis.';
            }

            if (empty($duree)) {
                $errors[] = 'La durée est requise.';
            }

            if (empty($description)) {
                $errors[] = 'La description est requise.';
            }

            if (empty($authorName)) {
                $errors[] = 'Le nom de l\'auteur est requis.';
            }

            if (empty($categorieMovieId)) {
                $errors[] = 'La catégorie du film est requise.';
            }

            if (empty($actorsIdsJson)) {
                $errors[] = 'Les acteurs sont requis.';
            }

            // Si des erreurs existent, retourner un message d'erreur
            if (count($errors) > 0) {
                return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Vérification de la catégorie
            $categorieMovie = $em->getRepository(CategoryMovie::class)->find((int)$categorieMovieId);
            if (!$categorieMovie) {
                return new JsonResponse(['error' => "CategoryMovie with ID ".$categorieMovieId." not found."], JsonResponse::HTTP_NOT_FOUND);
            }
        
            // Décoder la chaîne JSON des IDs d'acteurs
            if (is_string($actorsIdsJson)) {
                $actorsIds = json_decode($actorsIdsJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new JsonResponse(['error' => "La chaîne JSON des acteurs n'est pas valide."], JsonResponse::HTTP_BAD_REQUEST);
                }
            } else {
                return new JsonResponse(['error' => "La clé 'actors' doit être une chaîne JSON."], JsonResponse::HTTP_BAD_REQUEST);
            }
        
            // Vérification des acteurs
            $actors = [];
            foreach ($actorsIds as $actorId) {
                $actor = $em->getRepository(Actor::class)->find((int)$actorId);
                if ($actor) {
                    $actors[] = $actor;
                } else {
                    return new JsonResponse(['error' => "Actor with ID $actorId not found."], JsonResponse::HTTP_NOT_FOUND);
                }
            }
        
            // Création de l'objet Movie
            $movie = new Movie();
            $movie->setName($name);
            $movie->setDuree(new \DateTime($duree));
            $movie->setDescription($description);
            $movie->setAuthorName($authorName);
            $movie->setCategorieMovie($categorieMovie);
        
            // Ajout des acteurs au film
            foreach ($actors as $actor) {
                $movie->addActor($actor);
            }
        
            // Gestion de l'image
            $uploadedFile = $request->files->get('image');
            if ($uploadedFile) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/movies';
                $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
                try {
                    $uploadedFile->move($uploadDir, $newFilename);
                    $movie->setImage('/uploads/movies/' . $newFilename);
                } catch (\Exception $e) {
                    return new JsonResponse(['error' => 'File upload failed: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        
            // Validation des données
            $errors = $validator->validate($movie);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
            }
        
            // Sauvegarde en base de données
            $em->persist($movie);
            $em->flush();
            

            // Retourne le film créé
            return new JsonResponse($serializer->serialize($movie,'json',['groups' =>"getMovies"]), JsonResponse::HTTP_CREATED, [], true);
        }
        
        



            #[Route('/api/movies',name:'getAllMovies',methods:['GET'])]
            public function gatAllMovies(Request $request,MovieRepository $movieRepository,SerializerInterface $serializer)
            {
                $page = $request->get("page",1);
                $limit = $request->get("limit",5);
                $movies = $movieRepository->findAllPerPage($page,$limit);

                
                if(!$movies)
                    return new JsonResponse(null,JsonResponse::HTTP_NOT_FOUND,[]);
                $moviesJson = $serializer->serialize($movies,'json',['groups' =>"getMovies"]);

                /**@var User */
                $user= $this->getUser();
                $movieArray = json_decode($moviesJson, true);

                foreach ($movieArray as $key => $movie) {
                    $movieArray[$key]["userConnect"] = $user->getId();
                }

                $moviesJson = json_encode($movieArray);

                return new JsonResponse($moviesJson,JsonResponse::HTTP_OK,[],true);
            }


        #[Route('/api/movies/search',name:'searchMoviesByKey',methods:['GET'])]
        public function getFilteredMovies(Request $request,MovieRepository $movieRepository,SerializerInterface $serializer)
        {
            $searchKey = $request->get('searchKey');
            $page = $request->get("page",1);
            $limit = $request->get("limit",5);
            $movies = $movieRepository->findByKeyPerPage($page,$limit,$searchKey);
            //dd($movies);
            if(!$movies)
                return new JsonResponse(null,JsonResponse::HTTP_NOT_FOUND,[]);
            $moviesJson = $serializer->serialize($movies,'json',['groups' =>"getMovies"]);

            return new JsonResponse($moviesJson,JsonResponse::HTTP_OK,[],true);
        }

        #[Route('/api/movies/{id}',name:'app_movie_detail',methods:['GET'])]
        public function getMovie(Movie $movie,SerializerInterface $serializer)
        {
            if(!$movie)
                return new JsonResponse(null,JsonResponse::HTTP_NOT_FOUND,[],true);
            $movieJson = $serializer->serialize($movie,'json',[]);
            return new JsonResponse($movieJson,JsonResponse::HTTP_OK,[],true);
        }


        #[Route('api/movies/{id}', name: 'app_delete_detail', methods: ['DELETE'])]
        public function deleteMovie(?Movie $movie, EntityManagerInterface $em): JsonResponse
        {
            // Vérifier si le film existe
            if (!$movie) {
                return new JsonResponse(['error' => 'Movie not found'], JsonResponse::HTTP_NOT_FOUND);
            }
        
            // Si le film existe, procéder à la suppression
            $em->remove($movie);
            $em->flush();
        
            return new JsonResponse(['message' => 'Movie deleted successfully'], JsonResponse::HTTP_OK);
        }
    



        #[Route('/api/movies/{id}', name:'updateMovie',methods:["PUT"])]


        public function updateMovie(int $id,Request $request,EntityManagerInterface $em,ValidatorInterface $validator): JsonResponse {
            // Récupérer le film
            $movie = $em->getRepository(Movie::class)->find($id);
            if (!$movie) {
                return $this->json(['error' => "Film avec l'ID $id introuvable."], 404);
            }

            // Mettre à jour les champs
            $name = $request->request->get('name');
            $duree = $request->request->get('duree');
            $description = $request->request->get('description');
            $authorName = $request->request->get('authorName');
            $categorieMovieId = $request->request->get('categorieMovie');
            $actorsIds = $request->request->get('actors'); // Tableau attendu

            if ($name) $movie->setName($name);
            if ($duree) $movie->setDuree($duree);
            if ($description) $movie->setDescription($description);
            if ($authorName) $movie->setAuthorName($authorName);

            // Valider les données du film
            $errors = $validator->validate($movie);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], 400);
            }

            // Vérification et mise à jour de la catégorie
            if ($categorieMovieId) {
                $categorieMovie = $em->getRepository(CategoryMovie::class)->find((int)$categorieMovieId);
                if (!$categorieMovie) {
                    return $this->json(['error' => "CategoryMovie avec l'ID $categorieMovieId introuvable."], 404);
                }
                $movie->setCategorieMovie($categorieMovie);
            }

            // Gestion des acteurs
            if ($actorsIds) {
                if (!is_array($actorsIds)) {
                    return $this->json(['error' => 'Le champ "actors" doit être un tableau.'], 400);
                }

                // Supprimer les anciens acteurs
                foreach ($movie->getActors() as $actor) {
                    $movie->removeActor($actor);
                }

                // Ajouter les nouveaux acteurs
                foreach ($actorsIds as $actorId) {
                    $actor = $em->getRepository(Actor::class)->find((int)$actorId);
                    if (!$actor) {
                        return $this->json(['error' => "Acteur avec l'ID $actorId introuvable."], 404);
                    }
                    $movie->addActor($actor);
                }
            }

            // Persister les modifications
            $em->persist($movie);
            $em->flush();

            return $this->json(['message' => 'Film mis à jour avec succès.']);
        }

}
