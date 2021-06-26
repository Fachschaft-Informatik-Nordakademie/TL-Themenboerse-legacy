<?php

namespace App\Controller;

use App\Entity\StatusType;
use App\Entity\Topic;
use App\Repository\TopicRepository;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class TopicController extends AbstractController
{
    private TopicRepository $topicRepository;
    private ValidatorInterface $validator;
    private ParameterBagInterface $params;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(TopicRepository $topicRepository, ValidatorInterface $validator, ParameterBagInterface $params, MailerInterface $mailer, Environment $twig)
    {
        $this->topicRepository = $topicRepository;
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

        $topics = $this->topicRepository->listTopics($pageNumber, $pageSize, $orderBy, $orderDirection);
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
        $topic = $this->topicRepository->find($id);

        if (!$topic) {
            return $this->json(['message' => 'Topic not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($topic);
    }


    #[Route('/topic/{id}', name: 'topic_put', methods: ['put'])]
    public function updateTopic(Request $request, int $id): Response
    {
        $user = $this->getUser();
        $topic = $this->topicRepository->find($id);
        if ($topic->getAuthor()->getId() !== $user->getId()) {
            return $this->json(['message' => 'You are not allowed to edit this topic'], Response::HTTP_NOT_FOUND);
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
            return $this->json(['message' => 'Invalid topic received'], Response::HTTP_BAD_REQUEST);
        }
        $errors = $this->validator->validate($topic);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Invalid topic received'], Response::HTTP_BAD_REQUEST);
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
}
