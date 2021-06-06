<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserType;
use App\Repository\UserRepository;
use App\Security\ExternalJsonAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{

    private EntityManagerInterface $em;
    private ExternalJsonAuthenticator $jsonAuth;
    private UserPasswordEncoderInterface $pwEncoder;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, ExternalJsonAuthenticator $jsonAuth, UserPasswordEncoderInterface $pwEncoder, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->jsonAuth = $jsonAuth;
        $this->pwEncoder = $pwEncoder;
        $this->userRepository = $userRepository;
    }

    #[Route('/register', name: 'register', methods: ['post'])]
    public function register(Request $request): Response
    {
        $credentials = $this->jsonAuth->getCredentials($request); // TODO 
        $email = $credentials['email'];
        $password = $credentials['password'];
        if ($this->userRepository->loadUserByEmail($email)) {
            return $this->json(['message' => 'The e-mail address is already in use.'], Response::HTTP_BAD_REQUEST);
        }
        if (strlen($password) < 8) {
            return $this->json(['message' => 'The password must contain at least 8 characters.'], Response::HTTP_BAD_REQUEST);
        }
        $user = new User();
        $password = $this->pwEncoder->encodePassword($user, $password);
        $user->setEmail($email)->setPassword($password)->setType(UserType::EXTERNAL);
        $this->em->persist($user);
        $this->em->flush();
        return $this->json(['message' => 'Registered user ' . $email]);
    }
}
