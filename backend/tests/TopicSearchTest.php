<?php

namespace App\Tests;

use App\Entity\StatusType;
use App\Entity\Topic;
use App\Entity\User;
use Carbon\Carbon;

class TopicSearchTest extends SecureApiTestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_PAGE_SIZE'] = 20;
        parent::setUp();
        $date1 = Carbon::createFromDate(2021, 1, 1);
        $date2 = Carbon::createFromDate(2021, 2, 1);
        $date3 = Carbon::createFromDate(2021, 3, 1);
        $date4 = Carbon::createFromDate(2021, 4, 1);
        $date5 = Carbon::createFromDate(2021, 5, 1);
        $date4 = $date4->setHour(12);
        $this->topic1 = $this->createTopic(title: 'Nutzwertanalyse', start: $date1, end: $date3, tags: ['nutzwert']);
        $this->topic2 = $this->createTopic(title: 'Umfrage', start: $date2, end: $date4);
        $this->topic3 = $this->createTopic(title: 'Unit Test', start: $date1, end: $date5, tags: ['test']);
        $this->topic4 = $this->createTopic(title: 'Integration Test', tags: ['test']);
        $this->topic5 = $this->createTopic(title: 'Beta Test', tags: ['test']);
        $this->topic6 = $this->createTopic(title: 'Nutzwert', tags: ['nutzwert']);
        $this->topic7 = $this->createTopic(title: 'Themenbörse Masterprojekt', start: $date1->addDay());
        $this->topic8 = $this->createTopic(title: 'Künstliche Intelligenz');
        $this->topic9 = $this->createTopic(title: 'Management Bewertung', status: StatusType::LOCKED);
        $this->topic10 = $this->createTopic(title: '', status: StatusType::ASSIGNED);
    }

    private function createTopic(string $title = "Lorem ipsum", string $status = StatusType::OPEN, ?Carbon $start = null, ?Carbon $end = null, ?array $tags = null): Topic
    {
        /** @var User $user */
        $user = $this->em->find(User::class, 1);

        $topic = new Topic();
        $topic->setAuthor($user);
        $topic->setTitle($title);
        $topic->setDescription("Lorem ipsum dolor sit amet");
        $topic->setRequirements("Requirements");
        $topic->setScope("Scope");
        $topic->setWebsite("https://nordakademie.de");
        $topic->setStatus($status);
        $topic->setTags($tags);
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


    public function test_topic_list_returns_all_without_search_params(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(10, count($data["content"]));
    }


    public function test_topic_list_with_search_word(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?text=test');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(3, count($data["content"]));
    }


    public function test_topic_list_with_search_tags(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?tags=test');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(3, count($data["content"]));
    }

    public function test_topic_list_with_two_search_tags(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?tags=test,nutzwert');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(5, count($data["content"]));
    }

    public function test_topic_list_search_startUntil(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?startUntil=2021-01-15');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(9, count($data["content"]));
    }

    public function test_topic_list_search_startUntil_sameDay(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?startUntil=2021-01-01');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(8, count($data["content"]));
    }

    public function test_topic_list_search_startFrom(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?startFrom=2021-01-15');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(7, count($data["content"]));
    }

    public function test_topic_list_search_startFrom_same_day(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?startFrom=2021-02-01');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(7, count($data["content"]));
    }

    public function test_topic_list_search_startFrom_endFrom(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?startFrom=2021-01-02&startUntil=2021-02-15');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(8, count($data["content"]));
    }

    public function test_topic_list_search_endUntil(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?endUntil=2021-04-01');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(9, count($data["content"]));
    }

    public function test_topic_list_search_endFrom(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?endFrom=2021-04-01');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(9, count($data["content"]));
    }

    public function test_topic_list_search_endUntil_endFrom(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?endUntil=2021-04-15&endFrom=1999-04-01');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(9, count($data["content"]));
    }

    public function test_topic_list_search_startFrom_startUntil_endUntil_endFrom(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?startFrom=2021-01-02&startUntil=2021-06-01&endUntil=2021-04-15&endFrom=1999-04-01');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(8, count($data["content"]));
    }

    public function test_topic_list_search_only_open_false(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?onlyOpen=false');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(10, count($data["content"]));
    }

    public function test_topic_list_search_only_open_true(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic?onlyOpen=true');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(8, count($data["content"]));
    }
}
