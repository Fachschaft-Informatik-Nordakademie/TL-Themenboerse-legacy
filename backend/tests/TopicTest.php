<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Entity\UserType;
use App\Entity\Topic;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class AuthenticationTest extends ApiTestCase
{

    private Client $client;
    private EntityManagerInterface $em;

    use RecreateDatabaseTrait;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();


        $this->em = self::$container->get(EntityManagerInterface::class);
    }


    public function test_that_topic_creation_works(): void
    {
        $this->client->request('POST', '/topic', [
            'json' => [
                'title' => 'test title',
                'description' => 'This is a description.',
                'requirements' => 'This are the requirements',
                'tags' => "PHP",
                'deadline' => "04/10/2021 14:04:10",
                'pages' => 80,
                'start' => "04/06/2021 14:04:10"


            ],
        ]);;

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['message' => 'Successfully saved new topic']);
    }
}
