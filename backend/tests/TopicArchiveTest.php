<?php

namespace App\Tests;

use App\Entity\StatusType;
use Symfony\Component\HttpFoundation\Response;

class TopicArchiveTest extends SecureApiTestCase
{
    private function postDummyTopic(): int
    {
        $response = $this->client->request('POST', '/topic', [
            'json' => [
                'title' => 'test title',
                'scope' => 'test scope',
                'description' => 'This is a description.',
                'requirements' => 'These are the requirements'
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $id = json_decode($response->getContent())->{'id'};
        return $id;
    }

    public function test_that_topic_can_be_archived_and_unarchived(): void
    {
        $this->ensureLogin();
        $id = $this->postDummyTopic();

        $this->client->request('PUT', '/topic/' . $id . '/archive', [
            'json' => [
                'archive' => true
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'status' => StatusType::ARCHIVED
        ]);

        $this->client->request('PUT', '/topic/' . $id . '/archive', [
            'json' => [
                'archive' => false
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'status' => StatusType::OPEN
        ]);
    }

    public function test_archived_topic_cannot_receive_applications(): void
    {
        $this->ensureLoginExternal();
        $id = $this->postDummyTopic();

        $this->client->request('PUT', '/topic/' . $id . '/archive', [
            'json' => [
                'archive' => true
            ]
        ]);

        $this->ensureLoginLDAP();
        $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $id
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function test_only_own_topics_can_be_archived(): void
    {
        $this->ensureLoginExternal();
        $id = $this->postDummyTopic();
        $this->ensureLoginLDAP();

        $this->client->request('PUT', '/topic/' . $id . '/archive', [
            'json' => [
                'archive' => true
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
