<?php

namespace App\Controller;

use App\ResponseCodes;
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
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$AUTHENTICATION_FAILED), Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(ResponseCodes::makeResponse(ResponseCodes::$SUCCESS, ["user" => $user]));
    }

    #[Route('/logout', name: 'logout', methods: ['post'])]
    public function logout(): void
    {
        // nothing happens here
    }
}
