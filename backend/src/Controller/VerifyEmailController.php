<?php

namespace App\Controller;

use App\Event\SendConfirmationLinkEvent;
use App\Repository\UserRepository;
use App\ResponseCodes;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VerifyEmailController extends AbstractController
{
    public function __construct(private UserRepository $userRepository, private EntityManagerInterface $em, private EventDispatcherInterface $dispatcher)
    {
    }

    #[Route('/verify-email', methods: ['post'])]
    public function verify(Request $request): Response
    {
        $token = $request->get('token');

        $user = $this->userRepository->loadUserByVerificationToken($token);

        if (!$user) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$VERIFICATION_TOKEN_INVALID), Response::HTTP_BAD_REQUEST);
        }

        if ((new Carbon($user->getVerficationTokenExpires()))->isPast()) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$VERIFICATION_TOKEN_EXPIRED), Response::HTTP_BAD_REQUEST);
        }

        $user->setEmailVerified(true);
        $user->setVerificationToken(null);
        $user->setVerficationTokenExpires(null);

        $this->em->persist($user);
        $this->em->flush();

        return $this->json(ResponseCodes::makeResponse(ResponseCodes::$SUCCESS));
    }

    #[Route('/verify-email/resend', methods: ['post'])]
    public function resendEmail(Request $request): Response
    {
        $email = $request->get('email');

        $user = $this->userRepository->loadUserByEmail($email);

        if ($user !== null && !$user->isEmailVerified()) {
            $this->dispatcher->dispatch(new SendConfirmationLinkEvent($user));
        }

        return $this->json(ResponseCodes::makeResponse(ResponseCodes::$SUCCESS));
    }
}
