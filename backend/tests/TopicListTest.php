<?php

namespace App\Tests;

use App\Entity\StatusType;
use App\Entity\Topic;
use App\Entity\User;
use Carbon\Carbon;

class TopicListTest extends SecureApiTestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_PAGE_SIZE'] = 3;
        parent::setUp();

        $this->topic1 = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $this->topic2 = $this->createTopic(start: Carbon::now()->addDays(3), end: Carbon::now()->addDays(22));
        $this->topic3 = $this->createTopic(start: Carbon::now()->addDays(4));
        $this->topic4 = $this->createTopic(start: Carbon::now()->addDays(1), end: Carbon::now()->addDays(83));
        $this->topic5 = $this->createTopic(start: Carbon::now()->addDays(0), end: Carbon::now()->addDays(45));
        $this->topic6 = $this->createTopic(end: Carbon::now()->addDays(3));
        $this->topic7 = $this->createTopic(start: Carbon::now()->addDays(17), end: Carbon::now()->addDays(42));
        $this->topic8 = $this->createTopic(start: Carbon::now()->addDays(12), end: Carbon::now()->addDays(15));
        $this->topic9 = $this->createTopic(start: Carbon::now()->addDays(5), end: Carbon::now()->addDays(99));
        $this->topic10 = $this->createTopic(start: Carbon::now()->addDays(42));
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
        $topic->setWebsite("https://nordakademie.de");
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


    public function test_topic_list_returns_paginated_data(): void
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(3, $data["perPage"]);
        $this->assertEquals(4, $data["pages"]);
        $this->assertEquals(false, $data["last"]);
        $this->assertEquals(3, count($data["content"]));


        $response = $this->client->request('GET', '/topic?page=1');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(3, $data["perPage"]);
        $this->assertEquals(4, $data["pages"]);
        $this->assertEquals(false, $data["last"]);
        $this->assertEquals(3, count($data["content"]));


        $response = $this->client->request('GET', '/topic?page=2');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(3, $data["perPage"]);
        $this->assertEquals(4, $data["pages"]);
        $this->assertEquals(false, $data["last"]);
        $this->assertEquals(3, count($data["content"]));


        $response = $this->client->request('GET', '/topic?page=3');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data["total"]);
        $this->assertEquals(3, $data["perPage"]);
        $this->assertEquals(4, $data["pages"]);
        $this->assertEquals(true, $data["last"]);
        $this->assertEquals(1, count($data["content"]));
    }

    public function test_that_results_are_sorted_by_deadline_if_not_specified()
    {
        $this->ensureLogin();
        $response = $this->client->request('GET', '/topic');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($this->topic3->getId(), $data["content"][0]["id"]);
        $this->assertEquals($this->topic10->getId(), $data["content"][1]["id"]);
        $this->assertEquals($this->topic6->getId(), $data["content"][2]["id"]);


        $response = $this->client->request('GET', '/topic?page=1');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($this->topic8->getId(), $data["content"][0]["id"]);
        $this->assertEquals($this->topic2->getId(), $data["content"][1]["id"]);
        $this->assertEquals($this->topic1->getId(), $data["content"][2]["id"]);


        $response = $this->client->request('GET', '/topic?page=2');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($this->topic7->getId(), $data["content"][0]["id"]);
        $this->assertEquals($this->topic5->getId(), $data["content"][1]["id"]);
        $this->assertEquals($this->topic4->getId(), $data["content"][2]["id"]);


        $response = $this->client->request('GET', '/topic?page=3');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($this->topic9->getId(), $data["content"][0]["id"]);
    }
}