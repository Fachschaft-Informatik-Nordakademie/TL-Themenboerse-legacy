<?php


namespace App\Tests;


use App\Entity\StatusType;
use App\Entity\Topic;
use App\Entity\User;
use Carbon\Carbon;
use const true;

class FavoriteTest extends SecureApiTestCase
{

    protected function setUp(): void
    {
        $_ENV['APP_PAGE_SIZE'] = 3;
        parent::setUp();
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
        $topic->setWebsite("https://www.nordakademie.de");
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

    public function setFavorite(?int $topicId, bool $favorite): void
    {
        $this->client->request('PUT', '/topic/' . $topicId . '/favorite', [
            'json' => [
                'favorite' => $favorite
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function setFavoriteAndAssert(?int $topicId, bool $favorite): void
    {
        $this->setFavorite($topicId, $favorite);
        $response = $this->client->request('GET', '/topic/' . $topicId);
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);
        /*TODO: kp warum favorite nicht da ist? Wenn man die Anwendung normal startet, geht alles*/
        $this->assertEquals($favorite, $data["favorite"]);
    }

    public function test_add_and_remove_favorite_works(): void
    {
        $this->ensureLogin();
        $topic1 = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $topicId = $topic1->getId();

        $this->setFavoriteAndAssert($topicId, true);
        $this->setFavoriteAndAssert($topicId, false);
    }

    public function test_favorite_list(): void
    {
        $this->ensureLogin();

        $topic1 = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $topic2 = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));

        $this->setFavorite($topic1->getId(), true);
        $this->setFavorite($topic2->getId(), true);

        $response = $this->client->request('GET', '/topic?favorite=true');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(2, count($data["content"]));
        $this->assertEquals(2, $data["total"]);
        $this->assertEquals(1, $data["pages"]);
        $this->assertEquals(true, $data["last"]);
        $this->assertEquals(3, $data["perPage"]);
    }

}