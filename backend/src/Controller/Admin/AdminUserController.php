<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\ResponseCodes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted("ROLE_ADMIN")]
class AdminUserController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route("/admin/user", methods: ["get"])]
    public function listUsers(): Response
    {
        return $this->json(["data" => $this->userRepository->findAll()]);
    }

    #[Route("/admin/user/{userId}", methods: ["put"])]
    public function updateUser(int $userId, Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var User $user */
        $user = $this->userRepository->find($userId);

        if(!$user) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$USER_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        if($user->getId() === $currentUser->getId()) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$CANT_EDIT_OWN_USER), Response::HTTP_BAD_REQUEST);
        }

        $admin = $request->get('admin', $user->isAdmin());
        $user->setAdmin($admin);

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->json(["user" => $user]);
    }

}