<?php

namespace App\EventListener;

use App\Event\SendConfirmationLinkEvent;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class SendConfirmationLinkEventListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private ParameterBagInterface $params,
        private MailerInterface $mailer,
        private Environment $twig,
    )
    {
    }


    public function onSend(SendConfirmationLinkEvent $event)
    {
        $event->getUser()->setVerficationTokenExpires(Carbon::now()->addDay()->toDateTime());
        $event->getUser()->setVerificationToken(bin2hex(random_bytes(16)));

        $this->em->persist($event->getUser());
        $this->em->flush();

        // send email with confirmation link
        $mailContext = [
            "user" => $event->getUser(),
            "base_url" => $this->params->get('app.frontend_base_url'),
        ];

        $confirmationMail = (new TemplatedEmail())
            ->to($event->getUser()->getEmail())
            ->subject('Registrierung abschließen')
            ->htmlTemplate('email/email-confirmation.html.twig')
            ->text(str_replace("\n", "\r\n", $this->twig->render('email/email-confirmation.txt.twig', $mailContext)))
            ->context($mailContext);
        $this->mailer->send($confirmationMail);
    }
}
