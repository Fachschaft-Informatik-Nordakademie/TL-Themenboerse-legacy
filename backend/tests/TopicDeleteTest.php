<?php

namespace App\Tests;

use App\Entity\Application;
use App\Entity\StatusType;
use App\Entity\Topic;
use App\Entity\User;
use App\Repository\ApplicationRepository;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class TopicDeleteTest extends SecureApiTestCase
{
    private Topic $topic1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->topic1 = $this->createTopic(start: Carbon::now()->addDays(2), end: Carbon::now()->addDays(25));
        $this->em->createQueryBuilder()->update(User::class, 'u')
            ->set('u.admin', 1)
            ->where('u.email = :email')
            ->setParameter('email', 'dummy@example.com')
            ->getQuery()
            ->execute();
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


    public function test_delete_topic_is_denied_for_normal_user(): void
    {
        $this->ensureLoginExternal('dalen@example.com');

        $this->client->request('DELETE', '/topic/' . $this->topic1->getId(),  [
            'json' => [],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function test_delete_topic_is_denied_if_status_is_assigned(): void
    {
        $this->ensureLoginExternal('dummy@example.com');

        $this->em->createQueryBuilder()
            ->update(Topic::class, 't')
            ->set('t.status', ':status')
            ->where('t.id = :id')
            ->setParameter('id', $this->topic1->getId())
            ->setParameter('status', StatusType::ASSIGNED)
            ->getQuery()
            ->execute();

        $this->client->request('DELETE', '/topic/' . $this->topic1->getId(),  [
            'json' => [],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $t = $this->em->find(Topic::class, $this->topic1->getId());
        $this->assertNotNull($t);
    }

    public function test_delete_topic_works(): void
    {
        $this->ensureLoginExternal('dummy@example.com');

        $this->client->request('DELETE', '/topic/' . $this->topic1->getId(),  [
            'json' => [],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $t = $this->em->find(Topic::class, $this->topic1->getId());
        $this->assertNull($t);
    }

    public function test_deletion_also_deletes_applications(): void
    {
        $this->ensureLoginLDAP('10000');

        $this->client->request('POST', '/application', [
            'json' => [
                'topic' => $this->topic1->getId(),
                'content' => "Content"
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $this->ensureLoginExternal('dummy@example.com');
        $this->client->request('DELETE', '/topic/' . $this->topic1->getId(),  [
            'json' => [],
        ]);
        $this->assertResponseStatusCodeSame(200);

        // assert topic got deleted
        $t = $this->em->find(Topic::class, $this->topic1->getId());
        $this->assertNull($t);

        // assert applications got deleted
        $applications = $this->em->createQueryBuilder()
            ->from(Application::class, 'a')
            ->select('a')
            ->getQuery()
            ->getArrayResult();
        $this->assertEquals(0, count($applications));

        // check that user is still present
        $user = $this->em->createQueryBuilder()
            ->from(User::class, 'u')
            ->select('u')
            ->where('u.ldapUsername = :username')
            ->setParameter('username', '10000')
            ->getQuery()
            ->getOneOrNullResult();
        $this->assertNotNull($user);
    }


}
