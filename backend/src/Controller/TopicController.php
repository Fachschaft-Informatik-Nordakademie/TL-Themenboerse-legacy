<?php

namespace App\Controller;

use App\Entity\StatusType;
use App\Entity\Topic;
use App\Repository\TopicRepository;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TopicController extends AbstractController
{
    private TopicRepository $topicRepository;
    private ValidatorInterface $validator;
    private ParameterBagInterface $params;

    public function __construct(TopicRepository $topicRepository, ValidatorInterface $validator, ParameterBagInterface $params)
    {
        $this->topicRepository = $topicRepository;
        $this->validator = $validator;
        $this->params = $params;
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
        $user = $this->getUser();

        $status = StatusType::OPEN;

        if (!$user) {
            return $this->json(['message' => 'Authentication failed. You have to call this endpoint with a json body either containing email + password or username (ldap) + password'], Response::HTTP_UNAUTHORIZED);
        }

        $topic = new Topic();
        try {
            $topic->setAuthor($user);
            $topic->setTitle($request->get('title'));
            $topic->setDescription($request->get('description'));
            $topic->setRequirements($request->get('requirements'));
            $topic->setTags($request->get('tags'));
            $topic->setWebsite($request->get('website'));
            $topic->setScope($request->get('scope'));
            $deadline = $request->get('deadline');
            if ($deadline) {
                $topic->setDeadline(Carbon::parse($deadline)->toDate());
            }
            $start = $request->get('start');
            if ($start) {
                $topic->setStart(Carbon::parse($start)->toDate());
            }
            $topic->setPages($request->get('pages'));
            $topic->setStatus($status);
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

        return $this->json(['id' => $topic->getId()]);
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


        if (!$user) {
            return $this->json(['message' => 'Authentication failed. You have to call this endpoint with a json body either containing email + password or username (ldap) + password'], Response::HTTP_UNAUTHORIZED);
        }

        $topic = $this->topicRepository->find($id);


        if (!$topic) {
            return $this->json(['message' => 'Topic does not exist.'], Response::HTTP_UNAUTHORIZED);
        }


        try {
            /*$topic->setAuthor($user); */
            empty($request->get('title')) ? true : $topic->setTitle($request->get('title'));
            empty($request->get('description')) ? true : $topic->setDescription($request->get('description'));
            empty($request->get('requirements')) ? true : $topic->setRequirements($request->get('requirements'));
            empty($request->get('scope')) ? true : $topic->setScope($request->get('scope'));
            empty($request->get('tags')) ? true : $topic->setTags($request->get('tags'));
            empty($request->get('website')) ? true : $topic->setWebsite($request->get('website'));
            empty($request->get('deadline')) ? true : $deadline = $request->get('deadline');

            if ($deadline) {
                $topic->setDeadline(Carbon::parse($deadline)->toDate());
            }
            empty($request->get('start')) ? true : $start = $request->get('start');
            if ($start) {
                $topic->setStart(Carbon::parse($start)->toDate());
            }
            empty($request->get('pages')) ? true : $topic->setPages($request->get('pages'));
            empty($request->get('status')) ? true : $topic->setStatus($request->get('status'));
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

        return $this->json(['id' => $topic->getId()]);
    }
}
