<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserProfileController extends AbstractController
{
    private UserRepository $userRepository;
    private ValidatorInterface $validator;

    public function __construct(UserRepository $userRepository, ValidatorInterface $validator)
    {
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    #[Route('/user_profile', name: 'user_profile_put', methods: ['put'])]
    public function postProfile(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $profile = $user->getProfile();

        try {
            $profile
                ->setFirstName($request->get('firstName'))
                ->setLastName($request->get('lastName'))
                ->setImage($request->get('image'))
                ->setBiography($request->get('biography'))
                ->setCompany($request->get('company'))
                ->setJob($request->get('job'))
                ->setCourseOfStudy($request->get('courseOfStudy'))
                ->setSkills($request->get('skills'))
                ->setReferences($request->get('references'));
        } catch (\TypeError $e) {
            return $this->json(['message' => 'Invalid user profile received'], Response::HTTP_BAD_REQUEST);
        }
        $errors = $this->validator->validate($profile);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Invalid user profile received'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['id' => $user->getId()]);
    }

    #[Route('/user_profile/{id}', name: 'user_profile_get', methods: ['get'])]
    public function getProfile(int $id): Response
    {
        /** @var User $user */
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User profile not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($user->getProfile());
    }
}
