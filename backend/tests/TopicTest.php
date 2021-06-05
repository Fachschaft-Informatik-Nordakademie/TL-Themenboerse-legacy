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
                'description' => 'This is a description.',
                'requirements' => 'This are the requirements',
                'tags' => array("PHP"),
                'deadline' => "2021-10-04",
                'pages' => 80,
                'start' => "2021-06-04"
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function test_topic_getter(): void
    {
        $this->ensureLogin();

        $resp = $this->client->request('POST', '/topic', [
            'json' => [
                'title' => 'test title',
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
            'description' => 'This is a description.',
            'status' => 'OPEN'
        ]);
    }
}
