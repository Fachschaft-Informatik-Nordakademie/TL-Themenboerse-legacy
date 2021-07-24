<?php

namespace App\Tests;

use App\Entity\Application;
use App\Entity\ApplicationStatus;
use App\Entity\StatusType;
use App\Entity\Topic;

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

    public function test_approving_application_works(): void
    {
        $this->ensureLoginExternal();
        $topicId = $this->postDummyTopic();

        $this->ensureLoginLDAP('10000');
        $response = $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $topicId,
                'content' => 'Some Content'
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $applicationId1 = json_decode($response->getContent())->{'id'};

        $this->ensureLoginLDAP('20000');
        $response = $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $topicId,
                'content' => 'Some Content'
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $applicationId2 = json_decode($response->getContent())->{'id'};

        $this->ensureLoginLDAP('30000');
        $response = $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $topicId,
                'content' => 'Some Content'
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $applicationId3 = json_decode($response->getContent())->{'id'};

        $applications = $this->em->getRepository(Application::class)->findAll();
        $this->assertCount(3, $applications);

        $this->ensureLoginExternal();
        $this->client->request('PUT', '/application/' . $applicationId2, [
            'json' => [
                'status' => ApplicationStatus::ACCEPTED,
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var Topic $topic */
        $topic = $this->em->getRepository(Topic::class)->find($topicId);

        /** @var Application $application1 */
        $application1 = $this->em->getRepository(Application::class)->find($applicationId1);
        /** @var Application $application2 */
        $application2 = $this->em->getRepository(Application::class)->find($applicationId2);
        /** @var Application $application3 */
        $application3 = $this->em->getRepository(Application::class)->find($applicationId3);

        $this->assertEquals(ApplicationStatus::REJECTED, $application1->getStatus());
        $this->assertEquals(ApplicationStatus::ACCEPTED, $application2->getStatus());
        $this->assertEquals(ApplicationStatus::REJECTED, $application3->getStatus());

        $this->assertEquals(StatusType::ASSIGNED, $topic->getStatus());

    }
}
