<?php

namespace App\Controller;

use App\Entity\StatusType;
use App\Entity\Topic;
use App\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TopicController extends AbstractController
{
    private TopicRepository $topicRepository;
    private ValidatorInterface $validator;

    public function __construct(TopicRepository $topicRepository, ValidatorInterface $validator)
    {
        $this->topicRepository = $topicRepository;
        $this->validator = $validator;
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
        $topic->setAuthor($user);
        $topic->setTitle($request->get('title'));
        $topic->setDescription($request->get('description'));
        $topic->setRequirements($request->get('requirements'));
        $topic->setTags($request->get('tags'));
        $deadline = $request->get('deadline');
        if ($deadline) {
            $topic->setDeadline(\DateTime::createFromFormat('Y-m-d', $deadline));
        }
        $start = $request->get('start');
        if ($start) {
            $topic->setStart(\DateTime::createFromFormat('Y-m-d', $start));
        }
        $topic->setPages($request->get('pages'));
        $topic->setStatus($status);

        $errors = $this->validator->validate($topic);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Invalid topic received']);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($topic);
        $entityManager->flush();

        return $this->json(['id' => $topic->getId()]);
    }

    #[Route('/topic/{id}', name: 'topic_get', methods: ['get'])]
    public function getTopic(int $id): Response
    {
        $topic = $this->getDoctrine()
            ->getRepository(Topic::class)
            ->find($id);

        if (!$topic) {
            return $this->json(['message' => 'Topic not found'], Response::HTTP_UNAUTHORIZED);
        }
        return $this->json($topic);
    }
}
