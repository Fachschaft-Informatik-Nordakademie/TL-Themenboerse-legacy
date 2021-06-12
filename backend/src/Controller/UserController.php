<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    #[Route('/user', name: 'user-get', methods: ['get'])]
    public function test(): Response
    {
        $user = $this->getUser();

        return $this->json($user);
    }

}