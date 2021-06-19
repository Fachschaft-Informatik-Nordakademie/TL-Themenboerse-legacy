<?php

namespace App\Tests;

class TopicTest extends SecureApiTestCase
{
    public function test_that_topic_creation_works(): void
    {
        $this->ensureLogin();

        $this->client->request('POST', '/topic', [
            'json' => [
                'title' => 'test title',
                'scope' => 'test scope',
                'description' => 'This is a description.',
                'requirements' => 'These are the requirements',
                'tags' => array("PHP"),
                'deadline' => "2021-10-04",
                'pages' => 80,
                'start' => "2021-06-04"
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function test_topic_retrieved_with_id(): void
    {
        $this->ensureLogin();

        $resp = $this->client->request('POST', '/topic', [
            'json' => [
                'title' => 'test title',
                'scope' => 'test scope',
                'description' => 'This is a description.',
                'requirements' => 'These are the requirements',
                'tags' => array("PHP"),
                'deadline' => "2021-10-04",
                'pages' => 80,
                'start' => "2021-06-04"
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $id = json_decode($resp->getContent())->{'id'};
        $this->client->request('GET', '/topic/' . $id);
        $this->assertJsonContains([
            'title' => 'test title',
            'scope' => 'test scope',
            'description' => 'This is a description.',
            'requirements' => 'These are the requirements',
            'tags' => array("PHP"),
            'pages' => 80,
            'status' => 'OPEN'
        ]);
    }

    public function test_topic_empty_request_fails(): void
    {
        $this->ensureLogin();
        $this->client->request('POST', '/topic');
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_topic_malformed_title_fails(): void
    {
        $this->ensureLogin();

        $this->client->request('POST', '/topic', [
            'json' => [
                // Empty titles are not allowed
                'title' => ''
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_topic_malformed_date_fails(): void
    {
        $this->ensureLogin();

        $this->client->request('POST', '/topic', [
            'json' => [
                'title' => 'Correct Title',
                'start' => "a-b-c"
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_topic_unauthorized_access(): void
    {
        $this->ensureLogout();
        $this->client->request('POST', '/topic');
        $this->assertResponseStatusCodeSame(401);
    }

    public function test_topic_fails_retrieve(): void
    {
        $this->ensureLogin();
        $this->client->request('GET', '/topic/9999999');
        $this->assertResponseStatusCodeSame(404);
    }
}
