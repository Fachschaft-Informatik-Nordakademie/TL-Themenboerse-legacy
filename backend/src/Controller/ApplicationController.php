<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\User;
use App\Entity\Application;
use App\Entity\ApplicationStatus;
use App\Entity\StatusType;
use App\Repository\ApplicationRepository;
use App\Repository\TopicRepository;
use App\ResponseCodes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ApplicationController extends AbstractController
{
    private ApplicationRepository $applicationRepository;
    private TopicRepository $topicRepository;
    private ParameterBagInterface $params;
    private MailerInterface $mailer;
    private Environment $twig;
    private EntityManagerInterface $entityManager;

    public function __construct(ApplicationRepository $applicationRepository, TopicRepository $topicRepository, ParameterBagInterface $params, MailerInterface $mailer, Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->applicationRepository = $applicationRepository;
        $this->topicRepository = $topicRepository;
        $this->params = $params;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    #[Route('/application/{id}', name: 'application_delete', methods: ['delete'])]
    public function deleteApplication(int $id): Response
    {
        $user = $this->getUser();
        $this->applicationRepository->delete($user->getId(), $id);
        return $this->json([]);
    }

    #[Route('/application', name: 'application_post', methods: ['post'])]
    public function postApplication(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getLdapUsername()) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$APPLICATION_DENIED_ONLY_LDAP), Response::HTTP_UNAUTHORIZED);
        }

        $topicId = $request->get('topic');
        if (!$topicId) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$VALIDATION_FAILED), Response::HTTP_BAD_REQUEST);
        }

        /** @var Topic $topic */
        $topic = $this->topicRepository->find($topicId);

        if (!$topic) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$TOPIC_NOT_FOUND), Response::HTTP_BAD_REQUEST);
        } elseif ($topic->getStatus() !== StatusType::OPEN) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$TOPIC_NOT_OPEN_FOR_APPLICATIONS), Response::HTTP_BAD_REQUEST);
        } elseif ($topic->getAuthor()->getId() === $user->getId()) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$APPLICATION_DENIED_OWN_TOPIC), Response::HTTP_BAD_REQUEST);
        } elseif ($this->applicationRepository->hasCandidateForTopic($user->getId(), $topicId)) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$APPLICATION_DENIED_ALREADY_APPLIED), Response::HTTP_BAD_REQUEST);
        }

        $application = new Application();
        $application
            ->setCandidate($user)
            ->setContent($request->get('content'))
            ->setStatus(ApplicationStatus::OPEN)
            ->setTopic($topic);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($application);
        $entityManager->flush();

        $mailContext = [
            "application" => $application,
            "base_url" => $this->params->get('app.frontend_base_url'),
        ];

        $email = (new TemplatedEmail())
            ->to($topic->getAuthor()->getEmail())
            ->subject('Neue Bewerbung für ihr Thema \'' . $topic->getTitle() . '\'')
            ->htmlTemplate('email/new-application.html.twig')
            ->text(str_replace("\n", "\r\n", $this->twig->render('email/new-application.txt.twig', $mailContext)))
            ->context($mailContext);
        $this->mailer->send($email);

        return $this->json(['id' => (string)$application->getId()]);
    }

    #[Route('/application/{id}', methods: ['put'])]
    public function acceptApplication(int $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Application $application */
        $application = $this->applicationRepository->find($id);

        if (!$application) {
            return new JsonResponse(ResponseCodes::makeResponse(ResponseCodes::$APPLICATION_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        if ($application->getTopic()->getAuthor()->getId() !== $user->getId()) {
            return new JsonResponse(ResponseCodes::makeResponse(ResponseCodes::$APPLICATION_ACCEPT_PERMISSION_DENIED), Response::HTTP_FORBIDDEN);
        }

        if ($application->getTopic()->getStatus() !== StatusType::OPEN) {
            return new JsonResponse(ResponseCodes::makeResponse(ResponseCodes::$APPLICATION_ACCEPT_TOPIC_NOT_OPEN), Response::HTTP_BAD_REQUEST);
        }

        $topic = $application->getTopic();

        $entityManager = $this->getDoctrine()->getManager();

        [$acceptedUser, $deniedUsers] = $this->updateApplicationStatus($application);

        $application->getTopic()->setStatus(StatusType::ASSIGNED);
        $entityManager->persist($application->getTopic());
        $entityManager->flush();

        // send emails after entitymanager commit
        $this->sendApplicationAcceptedMail($topic, $acceptedUser);
        $this->sendApplicationDeniedMails($topic, $deniedUsers);

        return new JsonResponse(ResponseCodes::makeResponse(ResponseCodes::$SUCCESS), Response::HTTP_OK);
    }



    private function updateApplicationStatus(Application $acceptedApplication): array
    {
        $acceptedUser = null;
        $deniedUsers = [];

        /** @var Application[] $allApplications */
        $allApplications = $this->applicationRepository->findBy(['topic' => $acceptedApplication->getTopic()]);
        foreach ($allApplications as $singleApplication) {
            if ($singleApplication->getId() === $acceptedApplication->getId()) {
                $singleApplication->setStatus(ApplicationStatus::ACCEPTED);
                $acceptedUser = $singleApplication->getCandidate();
            } else {
                $singleApplication->setStatus(ApplicationStatus::REJECTED);
                array_push($deniedUsers, $singleApplication->getCandidate());
            }
            $this->entityManager->persist($singleApplication);
        }

        return [$acceptedUser, $deniedUsers];
    }

    private function sendApplicationAcceptedMail(Topic $topic, ?User $acceptedUser): void
    {
        $this->sendApplicationResultMail($topic, $acceptedUser, 'application-accepted');
    }

    private function sendApplicationDeniedMails(Topic $topic, array $deniedUsers): void
    {
        foreach ($deniedUsers as $du) {
            $this->sendApplicationResultMail($topic, $du, 'application-denied');
        }
    }

    private function sendApplicationResultMail(Topic $topic, User $user, string $templateName): void
    {
        $mailContext = [
            "topic" => $topic,
            "user" => $user,
            "base_url" => $this->params->get('app.frontend_base_url'),
        ];

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Dein Bewerbungsergebnis')
            ->htmlTemplate("email/${templateName}.html.twig")
            ->text(str_replace("\n", "\r\n", $this->twig->render("email/${templateName}.txt.twig", $mailContext)))
            ->context($mailContext);
        $this->mailer->send($email);
    }
}
