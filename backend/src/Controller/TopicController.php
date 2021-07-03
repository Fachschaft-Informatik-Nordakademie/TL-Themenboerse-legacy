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

        $topics = $this->topicRepository->listTopics($pageNumber, $pageSize, $orderBy, $orderDirection, $text, $tags, $onlyOpenBool, $startUntil, $startFrom, $endUntil, $endFrom);
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
        return $this->fillAndSaveTopic($topic, $request);
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
        return $this->json($topic);
    }


    #[Route('/topic/{id}', name: 'topic_put', methods: ['put'])]
    public function updateTopic(Request $request, int $id): Response
    {
        $user = $this->getUser();
        $topic = $this->topicRepository->find($id);
        if ($topic->getAuthor()->getId() !== $user->getId()) {
            return $this->json(ResponseCodes::makeResponse(ResponseCodes::$TOPIC_EDIT_PERMISSION_DENIED), Response::HTTP_NOT_FOUND);
        }
        return $this->fillAndSaveTopic($topic, $request);
    }

    function fillAndSaveTopic(Topic $topic, Request $request): Response
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
            "topic" => $topic,
            "base_url" => $this->params->get('app.frontend_base_url'),
        ];

        $email = (new TemplatedEmail())
            ->to($this->params->get('app.mail.new_topic_recipient'))
            ->subject('Neues Thema wurde eingereicht')
            ->htmlTemplate('email/new-topic.html.twig')
            ->text(str_replace("\n", "\r\n", $this->twig->render('email/new-topic.txt.twig', $mailContext)))
            ->context($mailContext);
        $this->mailer->send($email);

        return $this->json(['id' => $topic->getId()]);
    }

    private function getDate(Request $request, string $dateName): ?Carbon
    {
        $date = $request->get($dateName);
        if ($date === null) return null;
        if ($date !== null && empty(trim($date))) return null;
        return Carbon::parse($date);
    }
}
