<?php

// src/Controller/AuthController.php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MovieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request,SerializerInterface $serializer,EntityManagerInterface $em,ValidatorInterface $validator,MailerInterface $mailer,UserPasswordHasherInterface $userPasswordHasher,UserRepository $userRepository): JsonResponse {
        // Désérialiser et valider l'utilisateur
        // dd($request->getContent());
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        //dd($user);
        if ($userRepository->findOneByEmail($user->getEmail())) {
            return new JsonResponse(['message' => 'Email already used'], JsonResponse::HTTP_CONFLICT);
        }
        $errors = $validator->validate($user);
    
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(["errors" => $errorMessages],JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // Génération d'un code de vérification aléatoire
        
        $verificationCode = random_int(100000, 999999);
        $user->setCodeVerification($verificationCode);

        // Envoyer l'e-mail de vérification
        // $email = (new Email())
        //     ->from('noreply@votresite.com')
        //     ->to($user->getEmail())
        //     ->subject('Vérifiez votre compte')
        //     ->text("Votre code de vérification est : $verificationCode");

        // $mailer->send($email);

        $user->setPassword($userPasswordHasher->hashPassword($user,$user->getPassword()));

        // Enregistrer l'utilisateur en base de données avec un statut temporaire
        //$user->setSupp(true); // Marquez l'utilisateur comme inactif pour l'instant
        $em->persist($user);
        $em->flush();

        //return new JsonResponse($serializer->serialize($user,'json'), JsonResponse::HTTP_CREATED,[],true);
        return new JsonResponse(["message"=>"Creation avec succès"],JsonResponse::HTTP_CREATED,[],false);
    }

    #[Route('/api/verifyEmail', name: 'app_verify_email', methods: ['POST'])]
    public function verify(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;
        $code = $data['code'] ?? null;

        $user = $em->getRepository(User::class)->find($userId);

        if (!$user || $user->getCodeVerification() !== (int) $code) {
            return new JsonResponse(['message' => 'Code de vérification invalide.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Activation de l'utilisateur
        $user->setSupp(false); // Marquer l'utilisateur comme actif
        $user->setCodeVerification(null); // Effacer le code de vérification
        $em->flush();

        return new JsonResponse(['message' => 'Compte vérifié avec succès.'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request,JWTTokenManagerInterface $jwtManager,UserPasswordHasherInterface $passwordHasher,UserRepository $userRepository): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $userRepository->findOneByEmail($data['username']);

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['message' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token], JsonResponse::HTTP_OK);
    }

    #[Route('/api/favoryAdd/{id}',name:'user_fav_add', methods:['POST'])]
    public function addToFav(int $id,Request $request, EntityManagerInterface $entityManagerInterface, MovieRepository $movieRepository,SerializerInterface $serializer){

        $movie = $movieRepository->find($id);

        if(!$movie){
            return new JsonResponse(["errors" => " Movie not found"], JsonResponse::HTTP_BAD_REQUEST);
        }

        /** @var User $user*/
        $user = $this->getUser();

        $user->addFavory($movie);
        $entityManagerInterface->persist($user);
        $entityManagerInterface->flush();
        return new JsonResponse(["message"=>"successful Add"],JsonResponse::HTTP_CREATED,[],false);
    }

    #[Route('/api/favoryRemove/{id}',name:'user_fav_remove', methods:['POST'])]
    public function removeFromFav(int $id,Request $request, EntityManagerInterface $entityManagerInterface, MovieRepository $movieRepository,SerializerInterface $serializer){

        $movie = $movieRepository->find($id);

        if(!$movie){
            return new JsonResponse(["errors" => " Movie not found"], JsonResponse::HTTP_BAD_REQUEST);
        }

        /** @var User $user*/
        $user = $this->getUser();

        $user->removeFavory($movie);
        $entityManagerInterface->persist($user);
        $entityManagerInterface->flush();
        return new JsonResponse(["message"=>"successful Remove"],JsonResponse::HTTP_ACCEPTED,[],false);
    }


    #[Route('api/favMovie',name:"user_fav",methods:["GET"])]
    public function userFavories(SerializerInterface $serializerInterface,UserRepository $userRepository){
        /** @var User $user*/
        $user = $this->getUser();

        if(!$user){
            return new JsonResponse(["errors" => " User not found"], JsonResponse::HTTP_BAD_REQUEST);
        }

        $movies = $user->getFavories();
        $moviesJson = $serializerInterface->serialize($movies,'json',['groups' =>"getMovies"]);
        //dd($movies);

        return new JsonResponse($moviesJson,JsonResponse::HTTP_OK,[],true);
    }
    
    #[Route('api/user_info',name:"user_info",methods:["GET"])]
    public function getUsr(SerializerInterface $serializerInterface,UserRepository $userRepository){
        /** @var User $user*/
        $user = $this->getUser();

        if(!$user){
            return new JsonResponse(["errors" => " User not found"], JsonResponse::HTTP_BAD_REQUEST);
        }

        $userJson = $serializerInterface->serialize($user,'json',['groups' =>"getUser"]);
        //dd($movies);

        return new JsonResponse($userJson,JsonResponse::HTTP_OK,[],true);
    }

}


