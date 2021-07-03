<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\User;
use App\Entity\Application;
use App\Entity\ApplicationStatus;
use App\Entity\StatusType;
use App\Repository\ApplicationRepository;
use App\Repository\TopicRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ApplicationController extends AbstractController
{
    private ApplicationRepository $applicationRepository;
    private TopicRepository $topicRepository;
    private ParameterBagInterface $params;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(ApplicationRepository $applicationRepository, TopicRepository $topicRepository, ParameterBagInterface $params, MailerInterface $mailer, Environment $twig)
    {
        $this->applicationRepository = $applicationRepository;
        $this->topicRepository = $topicRepository;
        $this->params = $params;
        $this->mailer = $mailer;
        $this->twig = $twig;
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
            return $this->json(['message' => 'Only LDAP users are allowed to apply for open topics', 'user' => $user], Response::HTTP_UNAUTHORIZED);
        }

        $topicId = $request->get('topic');
        if (!$topicId) {
            return $this->json(['message' => 'No topic ID received.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Topic $topic */
        $topic = $this->topicRepository->find($topicId);

        if (!$topic) {
            return $this->json(['message' => 'A topic with this ID does not exist.'], Response::HTTP_BAD_REQUEST);
        } else if ($topic->getStatus() !== StatusType::OPEN) {
            return $this->json(['message' => 'This topic is not open for applications.'], Response::HTTP_BAD_REQUEST);
        } else if ($topic->getAuthor()->getId() === $user->getId()) {
            return $this->json(['message' => 'You cannot apply for your own topic.'], Response::HTTP_BAD_REQUEST);
        } else if ($this->applicationRepository->hasCandidateForTopic($user->getId(), $topicId)) {
            return $this->json(['message' => 'You already applied for this topic.'], Response::HTTP_BAD_REQUEST);
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
}
