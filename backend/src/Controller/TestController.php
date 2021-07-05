<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{

    #[Route('/test', name: 'test', methods: ['get'])]
    public function test(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'User is not set']);
        }

        return $this->json(['message' => 'You are logged in as ' . $user->getUsername()]);
    }

    #[Route(path: "/test-email")]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('themenboerse@example.com')
            ->to('you@example.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        return $this->json(["message" => "ok"]);
    }
}
