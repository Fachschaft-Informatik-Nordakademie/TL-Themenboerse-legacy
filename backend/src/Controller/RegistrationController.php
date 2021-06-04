<?php

namespace App\Controller;

use App\DataFixtures\Providers\HashPasswordProvider;
use App\Entity\User;
use App\Entity\UserType;
use App\Security\ExternalJsonAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{

    private EntityManagerInterface $em;
    private ExternalJsonAuthenticator $jsonAuth;
    private HashPasswordProvider $hashPw;

    public function __construct(EntityManagerInterface $em, ExternalJsonAuthenticator $jsonAuth, HashPasswordProvider $hashPw)
    {
        $this->em = $em;
        $this->jsonAuth = $jsonAuth;
        $this->hashPw = $hashPw;
    }

    #[Route('register', name: 'register', methods: ['post'])]
    public function register(Request $request): Response
    {
        $this->jsonAuth->supportsRegistration($request);
        $credentials = $this->jsonAuth->getCredentials($request);
        $email = $credentials['email'];
        $password = $this->hashPw->hashPassword($credentials['password']);
        $user = new User();
        $user->setEmail($email)->setPassword($password)->setType(UserType::EXTERNAL);
        $this->em->persist($user);
        $this->em->flush();
        return $this->json(['message' => 'Registered user  ' . $email]);
    }
}
