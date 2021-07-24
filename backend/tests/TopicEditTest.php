<?php

namespace App\Tests;

use App\Entity\StatusType;
use App\Entity\Topic;
use App\Entity\User;
use Carbon\Carbon;

class TopicEditTest extends SecureApiTestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_PAGE_SIZE'] = 3;
        parent::setUp();

        $this->topic1 = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
    }

    private function createTopic(string $title = "Lorem ipsum", string $status = StatusType::OPEN, ?Carbon $start = null, ?Carbon $end = null): Topic
    {
        /** @var User $user */
        $user = $this->em->find(User::class, 1);

        $topic = new Topic();
        $topic->setAuthor($user);
        $topic->setTitle($title);
        $topic->setDescription("Lorem ipsum dolor sit amet");
        $topic->setRequirements("Requirements");
        $topic->setScope("Scope");
        $topic->setWebsite("https::nordakademie.de");
        $topic->setStatus($status);
        if ($start) {
            $topic->setStart($start->toDate());
        }

        if ($end) {
            $topic->setDeadline($end->toDate());
        }
        $topic->setPages(42);

        $this->em->persist($topic);
        $this->em->flush();

        return $topic;
    }


    public function test_edit_topic__without_change_status_works(): void
    {
        $this->ensureLogin();
        $changedTopic = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $changedTopicId = $changedTopic->getId();

        $this->client->request('PUT', '/topic/' . $changedTopicId,  [
            'json' => [
                'title' => 'New Title',
                'description' => 'This is a different description.',
                'requirements' => 'These are the new requirements',
                'tags' => array("PHP", "React"),
                'deadline' => "2021-12-10",
                'pages' => 1000,
                'start' => "2021-10-04",
                'website' => 'https://github.com',
                'scope' => 'Change the topic',
                'status' => 'OPEN',

            ],
        ]);


        $response = $this->client->request('GET', '/topic/' . $changedTopicId);
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('New Title', $data["title"]);
        $this->assertEquals('This is a different description.', $data["description"]);
        $this->assertEquals('These are the new requirements', $data["requirements"]);
        $this->assertEquals(1000, $data["pages"]);
        $this->assertEquals('https://github.com', ($data["website"]));
        $this->assertEquals('2021-12-10T00:00:00+00:00', ($data["deadline"]));
        $this->assertEquals('2021-10-04T00:00:00+00:00', ($data["start"]));
        $this->assertEquals('Change the topic', ($data["scope"]));
        $this->assertEquals('OPEN', ($data["status"]));
    }

    public function test_edit_topic_change_status_works(): void
    {
        $this->ensureLogin();
        $changedTopic = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $changedTopicId = $changedTopic->getId();

        $this->client->request('PUT', '/topic/' . $changedTopicId,  [
            'json' => [
                'title' => 'New Title',
                'description' => 'This is a different description.',
                'requirements' => 'These are the new requirements',
                'tags' => array("PHP", "React"),
                'deadline' => "2021-12-10",
                'pages' => 1000,
                'start' => "2021-10-04",
                'website' => 'https://github.com',
                'scope' => 'Change the topic',
                'status' => 'ASSIGNED',

            ],
        ]);


        $response = $this->client->request('GET', '/topic/' . $changedTopicId);
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('New Title', $data["title"]);
        $this->assertEquals('This is a different description.', $data["description"]);
        $this->assertEquals('These are the new requirements', $data["requirements"]);
        $this->assertEquals(1000, $data["pages"]);
        $this->assertEquals('https://github.com', $data["website"]);
        $this->assertEquals('Change the topic', $data["scope"]);
        $this->assertEquals('ASSIGNED', $data["status"]);
        $this->assertEquals('2021-12-10T00:00:00+00:00', $data["deadline"]);
        $this->assertEquals('2021-10-04T00:00:00+00:00', $data["start"]);
    }

    public function test_empty_field_will_be_empty(): void
    {
        $this->ensureLogin();
        $changedTopic = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $changedTopicId = $changedTopic->getId();

        $this->client->request('PUT', '/topic/' . $changedTopicId,  [
            'json' => [
                'title' => 'a',
                'description' => '',
                'requirements' => '',
                'tags' => [],
                'deadline' => "",
                'pages' => 0,
                'start' => "",
                'website' => '',
                'scope' => '',
                'status' => '',

            ],
        ]);


        $response = $this->client->request('GET', '/topic/' . $changedTopicId);
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('a', $data["title"]);
        $this->assertEquals('', $data["description"]);
        $this->assertEquals('', $data["requirements"]);
        $this->assertEquals(0, $data["pages"]);
        $this->assertEquals('', $data["website"]);
        $this->assertEquals('', $data["scope"]);
        $this->assertEquals('', $data["status"]);
    }

    public function test_title_not_submitted(): void
    {
        $this->ensureLogin();
        $changedTopic = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $changedTopicId = $changedTopic->getId();

        $this->client->request('PUT', '/topic/' . $changedTopicId,  [
            'json' => [
                'description' => '',
                'requirements' => '',
                'tags' => [],
                'deadline' => "",
                'pages' => 0,
                'start' => "",
                'website' => '',
                'scope' => '',
                'status' => '',
            ],
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_validation_error_title_empty(): void
    {
        $this->ensureLogin();
        $changedTopic = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $changedTopicId = $changedTopic->getId();

        $this->client->request('PUT', '/topic/' . $changedTopicId,  [
            'json' => [
                'title' => '',
                'description' => '',
                'requirements' => '',
                'tags' => [],
                'deadline' => "",
                'pages' => 0,
                'start' => "",
                'website' => '',
                'scope' => '',
                'status' => '',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_edit_topic__with_admin_works(): void
    {
        $this->ensureLoginExternal('dummy@example.com');
        $changedTopic = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $changedTopicId = $changedTopic->getId();

        $this->em->createQueryBuilder()
            ->update(User::class, 'u')
            ->set('u.admin', true)
            ->where('u.email = :email')
            ->setParameter('email', 'dalen@example.com')
            ->getQuery()
            ->execute();

        $this->ensureLoginExternal('dalen@example.com');
        $this->client->request('PUT', '/topic/' . $changedTopicId,  [
            'json' => [
                'title' => 'New Title',
                'description' => 'This is a different description.',
                'requirements' => 'These are the new requirements',
                'tags' => array("PHP", "React"),
                'deadline' => "2021-12-10",
                'pages' => 1000,
                'start' => "2021-10-04",
                'website' => 'https://github.com',
                'scope' => 'Change the topic',
                'status' => 'OPEN',

            ],
        ]);


        $response = $this->client->request('GET', '/topic/' . $changedTopicId);
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('New Title', $data["title"]);
        $this->assertEquals('This is a different description.', $data["description"]);
        $this->assertEquals('These are the new requirements', $data["requirements"]);
        $this->assertEquals(1000, $data["pages"]);
        $this->assertEquals('https://github.com', ($data["website"]));
        $this->assertEquals('2021-12-10T00:00:00+00:00', ($data["deadline"]));
        $this->assertEquals('2021-10-04T00:00:00+00:00', ($data["start"]));
        $this->assertEquals('Change the topic', ($data["scope"]));
        $this->assertEquals('OPEN', ($data["status"]));
    }

    public function test_edit_topic__with_another_user_is_denied(): void
    {
        $this->ensureLoginExternal('dummy@example.com');
        $changedTopic = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $changedTopicId = $changedTopic->getId();

        $this->ensureLoginExternal('schroedinger@example.com');
        $this->client->request('PUT', '/topic/' . $changedTopicId,  [
            'json' => [
                'title' => 'New Title',
                'description' => 'This is a different description.',
                'requirements' => 'These are the new requirements',
                'tags' => array("PHP", "React"),
                'deadline' => "2021-12-10",
                'pages' => 1000,
                'start' => "2021-10-04",
                'website' => 'https://github.com',
                'scope' => 'Change the topic',
                'status' => 'OPEN',

            ],
        ]);

        $this->assertResponseStatusCodeSame(403);
    }
}
