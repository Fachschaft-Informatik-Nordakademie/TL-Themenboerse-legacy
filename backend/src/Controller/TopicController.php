<?php

namespace App\Controller;

use App\Entity\StatusType;
use App\Entity\Topic;
use App\Entity\User;
use App\Repository\ApplicationRepository;
use App\Repository\TopicRepository;
use App\ResponseCodes;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class TopicController extends AbstractController
{
    private TopicRepository $topicRepository;
    private ApplicationRepository $applicationRepository;
    private ValidatorInterface $validator;
    private ParameterBagInterface $params;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(TopicRepository $topicRepository, ApplicationRepository $applicationRepository, ValidatorInterface $validator, ParameterBagInterface $params, MailerInterface $mailer, Environment $twig)
    {
        $this->topicRepository = $topicRepository;
        $this->applicationRepository = $applicationRepository;
        $this->validator = $validator;
        $this->params = $params;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    #[Route('/topic', name: 'topic_list', methods: ['get'])]
    public function listTopics(Request $request): Response
    {
        $pageSize = $this->params->get('app.page_size');
        $pageNumber = intval($request->get('page') ?? '0') ?? 0;
        $isFavorite = $request->get('favorite', false);

        $orderBy = $request->get('orderBy') ?? 'deadline';
        $orderDirection = $request->get('order') ?? 'asc';

        $text = $request->get('text');
        $tags = $request->get('tags');
        $onlyOpen = $request->get('onlyOpen');
        $onlyOpenBool = filter_var($onlyOpen, FILTER_VALIDATE_BOOLEAN);

        $startUntil = $this->getDate($request, 'startUntil');
        $startFrom = $this->getDate($request, 'startFrom');
        $endUntil = $this->getDate($request, 'endUntil');
        $endFrom = $this->getDate($request, 'endFrom');

        if ($tags !== null && empty(trim($tags))) {
            $tags = null;
        }

        if ($text !== null && empty(trim($text))) {
            $text = null;
        }

        $user = $this->getUser();
        $topics = $this->topicRepository->listTopics($isFavorite, $user->getId(), $pageNumber, $pageSize, $orderBy, $orderDirection, $text, $tags, $onlyOpenBool, $startUntil, $startFrom, $endUntil, $endFrom);
        $totalAmount = $this->topicRepository->count([]);
        $totalPages = (int)ceil($totalAmount / $pageSize);

        return $this->json([
            "content" => $topics,
            "total" => $totalAmount,
            "pages" => max($totalPages, 1),
            "last" => $pageNumber === ($totalPages - 1),
            "perPage" => $pageSize,
        ]);
    }

    #[Route('/topic', name: 'topic_post', methods: ['post'])]
    public function postTopic(Request $request): Response
    {
        $topic = new Topic();
        $user = $this->getUser();
        $topic->setAuthor($user);
        return $this->fillAndSaveTopic($topic, $request, true);
    }

    #[Route('/topic/{id}', name: 'topic_get', methods: ['get'])]
    public function getTopic(int $id): Response
    {
        /** @var Topic $topic */
        $topic = $this->topicRepository->find($id);

        if (!$topic) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$TOPIC_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();
        $topic->setHasApplied($this->applicationRepository->hasCandidateForTopic($user->getId(), $topic->getId()));
        $topic->setFavorite($topic->hasFavoriteUser($user->getId()));

        // Fetch applications only if user is author
        if($user->getId() === $topic->getAuthor()->getId()) {
            $applications = $this->applicationRepository->findByTopic($topic);
            $topic->setApplications($applications);
        }

        return $this->json($topic);
    }


    #[Route('/topic/{id}', name: 'topic_put', methods: ['put'])]
    public function updateTopic(Request $request, int $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $topic = $this->topicRepository->find($id);
        if ($topic->getAuthor()->getId() !== $user->getId() && !$user->isAdmin()) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$TOPIC_EDIT_PERMISSION_DENIED), Response::HTTP_FORBIDDEN);
        }
        return $this->fillAndSaveTopic($topic, $request, false);
    }

    #[Route('/topic/{id}', name: 'topic_delete', methods: ['delete'])]
    public function deleteTopic(Request $request, int $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var Topic $topic */
        $topic = $this->topicRepository->find($id);

        if ($topic === null) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$TOPIC_NOT_FOUND), Response::HTTP_NOT_FOUND);
        }

        if (!$user->isAdmin()) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$TOPIC_DELETE_PERMISSION_DENIED), Response::HTTP_FORBIDDEN);
        }

        if ($topic->getStatus() === StatusType::ASSIGNED) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$TOPIC_DELETE_ALREADY_ASSIGNED), Response::HTTP_FORBIDDEN);
        }

        $topic->getAuthor()->getTopics()->removeElement($topic);
        $topic->setAuthor(null);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($topic);
        $entityManager->flush();
        return $this->json(ResponseCodes::makeResponse(ResponseCodes::$SUCCESS), Response::HTTP_OK);
    }

    public function fillAndSaveTopic(Topic $topic, Request $request, bool $new): Response
    {
        try {
            $topic->setTitle($request->get('title'));
            $topic->setDescription($request->get('description'));
            $topic->setRequirements($request->get('requirements'));
            $topic->setScope($request->get('scope'));
            $topic->setTags($request->get('tags'));
            $topic->setWebsite($request->get('website'));
            $deadline = $request->get('deadline');

            if ($deadline) {
                $topic->setDeadline(Carbon::parse($deadline)->toDate());
            }
            $start = $request->get('start');
            if ($start) {
                $topic->setStart(Carbon::parse($start)->toDate());
            }
            $topic->setPages($request->get('pages'));
            $topic->setStatus($request->get('status') ?? StatusType::OPEN);
        } catch (\TypeError | InvalidFormatException $e) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$VALIDATION_FAILED), Response::HTTP_BAD_REQUEST);
        }
        $errors = $this->validator->validate($topic);
        if (count($errors) > 0) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$VALIDATION_FAILED), Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($topic);
        $entityManager->flush();

        $mailContext = [
            "new" => $new,
            "topic" => $topic,
            "base_url" => $this->params->get('app.frontend_base_url'),
        ];

        $email = (new TemplatedEmail())
            ->to($this->params->get('app.mail.new_topic_recipient'))
            ->subject($new ? 'Neues Thema wurde eingereicht' : 'Ein Thema wurde bearbeitet')
            ->htmlTemplate('email/new-topic.html.twig')
            ->text(str_replace("\n", "\r\n", $this->twig->render('email/new-topic.txt.twig', $mailContext)))
            ->context($mailContext);
        $this->mailer->send($email);


        return $this->json(['id' => $topic->getId()]);
    }

    private function getDate(Request $request, string $dateName): ?Carbon
    {
        $date = $request->get($dateName);
        if ($date === null) {
            return null;
        }
        if ($date !== null && empty(trim($date))) {
            return null;
        }
        return Carbon::parse($date);
    }

    #[Route('/topic/{topicId}/favorite', name: 'topic_favorite_put', methods: ['put'])]
    public function updateFavorite(Request $request, int $topicId): Response
    {
        $topic = $this->topicRepository->find($topicId);
        if (!$topic) {
            return $this->json(['message' => 'Topic not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        $userId = $user->getId();
        $isFavorite = $request->get('favorite');
        if ($isFavorite && !$topic->hasFavoriteUser($userId)) {
            $topic->addFavoriteUser($user);
        } elseif (!$isFavorite && $topic->hasFavoriteUser($userId)) {
            $topic->removeFavoriteUser($user);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($topic);
    }
}
