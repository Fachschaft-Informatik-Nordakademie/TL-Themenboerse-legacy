<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

abstract class SecureApiTestCase extends ApiTestCase
{
    protected Client $client;
    protected EntityManagerInterface $em;

    use RecreateDatabaseTrait;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->em = self::$container->get(EntityManagerInterface::class);
    }

    protected function ensureLogin(): void
    {
        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'external',
                'email' => 'dummy@example.com',
                'password' => 'password',
            ],
        ]);
    }
}
