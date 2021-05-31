<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['post'])]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Authentication failed. You have to call this endpoint with a json body either containing email + password or username (ldap) + password'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(['message' => 'Welcome ' . $user->getUsername()]);
    }

    #[Route('/test', name: 'test', methods: ['get'])]
    public function test(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'User is not set']);
        }

        return $this->json(['message' => 'You are logged in as ' . $user->getUsername()]);
    }

    #[Route('/logout', name: 'logout', methods: ['post'])]
    public function logout(): void
    {
        // nothing happens here
    }
}
