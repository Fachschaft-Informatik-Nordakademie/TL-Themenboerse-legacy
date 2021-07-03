<?php

namespace App\Tests;

use App\Entity\Application;

class ApplicationControllerTest extends SecureApiTestCase
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
        $id = json_decode($response->getContent())->{'id'};
        return $id;
    }

    public function test_application_without_content(): void
    {
        $this->ensureLoginExternal();
        $id = $this->postDummyTopic();
        $this->ensureLoginLDAP();

        $response = $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $id
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $applicationId = json_decode($response->getContent())->{'id'};

        /** @var Application $application */
        $application = $this->em->getRepository(Application::class)->find($applicationId);
        $this->assertNotNull($application);
        $this->assertNull($application->getContent());
    }

    public function test_application_creation_with_content(): void
    {
        $this->ensureLoginExternal();
        $id = $this->postDummyTopic();
        $this->ensureLoginLDAP();

        $response = $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $id,
                'content' => 'Some Content'
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $applicationId = json_decode($response->getContent())->{'id'};

        /** @var Application $application */
        $application = $this->em->getRepository(Application::class)->find($applicationId);
        $this->assertNotNull($application);
        $this->assertEquals($application->getContent(), 'Some Content');
    }

    public function test_application_forbidden_from_topic_author(): void
    {
        $this->ensureLoginLDAP();
        $id = $this->postDummyTopic();

        $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $id
            ],
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_application_external_user_cant_apply(): void
    {
        $this->ensureLoginLDAP();
        $id = $this->postDummyTopic();
        $this->ensureLoginExternal();

        $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $id
            ],
        ]);
        $this->assertResponseStatusCodeSame(401);
    }

    public function test_application_no_duplicate(): void
    {
        $this->ensureLoginExternal();
        $id = $this->postDummyTopic();
        $this->ensureLoginLDAP();

        $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $id
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $id
            ],
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_application_empty_request_fails(): void
    {
        $this->ensureLoginLDAP();
        $this->client->request('POST', '/application');
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_application_missing_topic_fails(): void
    {
        $this->ensureLoginLDAP();

        $this->client->request('POST', '/application', [
            'json' => [
                // Do not enter a topic id
                'content' => ''
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_application_non_existing_topic_fails(): void
    {
        $this->ensureLoginLDAP();

        $this->client->request('POST', '/application', [
            'json' => [
                'topic' => 99999,
                'content' => "Content"
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_application_unauthorized_access(): void
    {
        $this->ensureLogout();
        $this->client->request('POST', '/application');
        $this->assertResponseStatusCodeSame(401);
    }

    public function test_application_delete(): void
    {
        $this->ensureLoginExternal();
        $id = $this->postDummyTopic();
        $this->ensureLoginLDAP();

        $response = $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $id
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $applicationId = json_decode($response->getContent())->{'id'};

        $this->client->request('DELETE', '/application/' . $id);

        $application = $this->em->getRepository(Application::class)->find($applicationId);
        $this->assertNull($application);
    }
}
