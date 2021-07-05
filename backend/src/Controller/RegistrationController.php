<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Entity\UserType;
use App\Event\SendConfirmationLinkEvent;
use App\Repository\UserRepository;
use App\ResponseCodes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordEncoderInterface $pwEncoder,
        private UserRepository $userRepository,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $dispatcher,
    ) {

    }

    #[Route('/register', name: 'register', methods: ['post'])]
    public function register(Request $request): Response
    {
        $email = $request->get('email', '');
        $password = $request->get('password');
        $firstName = $request->get('firstName', '');
        $lastName = $request->get('lastName', '');

        $user = new User();
        $user->setProfile(new UserProfile());
        $user->getProfile()->setUser($user);
        $user->setEmail($email)->setType(UserType::EXTERNAL);
        $user->getProfile()->setFirstName($firstName)->setLastName($lastName);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$VALIDATION_FAILED, ["validation_errors" => $errors]), Response::HTTP_BAD_REQUEST);
        }
        if ($this->userRepository->loadUserByEmail($email)) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$EMAIL_ALREADY_IN_USE), Response::HTTP_BAD_REQUEST);
        }
        if (strlen($password) < 8) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$PASSWORD_TOO_SHORT), Response::HTTP_BAD_REQUEST);
        }
        $password = $this->pwEncoder->encodePassword($user, $password);
        $user->setPassword($password);
        $user->setEmailVerified(false);
        $this->em->persist($user);
        $this->em->flush();

        $this->dispatcher->dispatch(new SendConfirmationLinkEvent($user));

        return $this->json(ResponseCodes::makeResponse(ResponseCodes::$SUCCESS));
    }
}
