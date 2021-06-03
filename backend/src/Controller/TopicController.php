<?php

namespace App\Controller;

use App\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TopicController extends AbstractController
{
    #[Route('/topic', name: 'topic', methods: ['post'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Authentication failed. You have to call this endpoint with a json body either containing email + password or username (ldap) + password'], Response::HTTP_UNAUTHORIZED);
        }

        $topic = new Topic();
        $topic->setAuthor($user);
        $topic->setTitle($request->get('title'));
        $topic->setDescription($request->get('description'));
        $topic->setRequirements($request->get('requirements'));
        $topic->setTags($request->get('tags'));
        $topic->setDeadline($request->get('deadline'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($topic);
        $entityManager->flush();

        return $this->json(['message' => 'Successfully saved new topic']);
    }

    #[Route('/topic/:id', name: 'topic', methods: ['get'])]
    public function getTopic(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Authentication failed. You have to call this endpoint with a json body either containing email + password or username (ldap) + password'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(['message' => 'Welcome ' . $user->getUsername()]);
    }
}
