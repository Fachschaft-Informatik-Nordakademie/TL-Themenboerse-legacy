<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class RegistrationControllerTest extends ApiTestCase
{

    private Client $client;

    use RecreateDatabaseTrait;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
    }

    public function test_that_register_throws_error_when_no_json_is_submitted(): void
    {
        $this->client->request('POST', '/register');

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_that_registration_works(): void
    {
        $this->client->request('POST', '/register', [
            'json' => [
                'email' => 'user1@example.com',
                'password' => 'password1',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'message' => 'Registered user user1@example.com',
        ]);
    }

    public function test_that_registered_user_can_login(): void
    {
        $this->client->request('POST', '/register', [
            'json' => [
                'email' => 'user1@example.com',
                'password' => 'password1',
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);

        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'external',
                'email' => 'user1@example.com',
                'password' => 'password1',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function test_that_register_throws_error_when_password_is_too_short(): void
    {
        $this->client->request('POST', '/register', [
            'json' => [
                'email' => 'user2@example.com',
                'password' => '1234567',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['message' => 'The password must contain at least 8 characters.']);
    }

    public function test_that_register_throws_error_when_email_is_used(): void
    {
        $this->client->request('POST', '/register', [
            'json' => [
                'email' => 'user3@example.com',
                'password' => 'password3',
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);

        $this->client->request('POST', '/register', [
            'json' => [
                'email' => 'user3@example.com',
                'password' => 'password3',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['message' => 'The e-mail address is already in use.']);
    }

    public function test_that_register_throws_error_when_email_is_not_valid(): void
    {
        $this->client->request('POST', '/register', [
            'json' => [
                'email' => 'user4',
                'password' => 'password4',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['message' => 'The e-mail address is not valid.']);
    }
}
