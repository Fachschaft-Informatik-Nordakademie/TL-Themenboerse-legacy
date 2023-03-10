<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Entity\UserType;
use App\ResponseCodes;
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

    public function test_that_controller_returns_error_response_when_not_authenticated(): void
    {
        $this->client->request('GET', '/test');

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['code' => ResponseCodes::$UNAUTHENTICATED]);
    }

    public function test_that_login_throws_error_when_no_json_is_submitted(): void
    {
        $this->client->request('POST', '/login');

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['code' => ResponseCodes::$AUTHENTICATION_FAILED]);
    }

    public function test_that_login_works_with_external_user(): void
    {
        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'external',
                'email' => 'dummy@example.com',
                'password' => 'password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'user' => [
                'profile' => [
                    'firstName' => 'Test',
                    'lastName' => 'Test'
                ]
            ],
        ]);

        $this->client->request('GET', '/test');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'message' => 'You are logged in as dummy@example.com',
        ]);
    }

    public function test_that_login_works_with_ldap_user(): void
    {
        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'ldap',
                'username' => '10000',
                'password' => 'secret',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'user' => [
                'profile' => [
                    'firstName' => 'Max',
                    'lastName' => 'Mustermann'
                ]
            ],
        ]);

        $this->client->request('GET', '/test');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'message' => 'You are logged in as max.mustermann@awesome-university.com',
        ]);
    }


    public function test_that_login_throws_error_on_wrong_password_external(): void
    {
        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'external',
                'email' => 'dummy@example.com',
                'password' => 'not-my-password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'code' => ResponseCodes::$INVALID_CREDENTIALS,
        ]);

        $this->client->request('GET', '/test');
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['code' => ResponseCodes::$UNAUTHENTICATED]);
    }

    public function test_that_login_throws_error_on_wrong_password_ldap(): void
    {
        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'ldap',
                'username' => '10000',
                'password' => 'secret-but-wrong',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'code' => ResponseCodes::$INVALID_CREDENTIALS,
        ]);

        $this->client->request('GET', '/test');
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['code' => ResponseCodes::$UNAUTHENTICATED]);
    }

    public function test_that_correct_user_is_set_after_logging_in_again(): void
    {
        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'ldap',
                'username' => '10000',
                'password' => 'secret',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'user' => [
                'profile' => [
                    'firstName' => 'Max',
                    'lastName' => 'Mustermann'
                ]
            ],
        ]);

        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'ldap',
                'username' => '20000',
                'password' => 'secret',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'user' => [
                'profile' => [
                    'firstName' => 'Mareike',
                    'lastName' => 'Musterfrau'
                ]
            ],
        ]);

        $this->client->request('GET', '/test');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'message' => 'You are logged in as mareike.musterfrau@awesome-university.com',
        ]);
    }

    public function test_that_ldap_user_is_inserted_into_db_if_it_does_not_exist(): void
    {
        $countQuery = $this->em->createQueryBuilder()
            ->from(User::class, 'u')
            ->select('count(u.id)')
            ->where('u.type = :type')
            ->setParameter('type', UserType::LDAP)
            ->getQuery();

        $this->assertEquals(0, $countQuery->getSingleScalarResult());

        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'ldap',
                'username' => '10000',
                'password' => 'secret',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'user' => [
                'profile' => [
                    'firstName' => 'Max',
                    'lastName' => 'Mustermann'
                ]
            ],
        ]);

        $this->assertEquals(1, $countQuery->getSingleScalarResult());
    }

    public function test_that_ldap_user_is_updated_on_login(): void
    {
        $user = new User();
        $user->setProfile(new UserProfile());
        $user->getProfile()->setUser($user);
        $user->setType(UserType::LDAP);
        $user->setEmail("not-his-real-email@awesome-university.com");
        $user->setLdapUsername("10000");
        $user->setLdapDn("cn=10000,ou=students,ou=people,dc=awesome-university,dc=com");
        $user->getProfile()->setFirstName("Test");
        $user->getProfile()->setLastName("Test");
        $user->setEmailVerified(true);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'ldap',
                'username' => '10000',
                'password' => 'secret',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'user' => [
                'profile' => [
                    'firstName' => 'Max',
                    'lastName' => 'Mustermann'
                ]
            ],
        ]);

        $this->em->refresh($user);
        $this->assertEquals('max.mustermann@awesome-university.com', $user->getEmail());
    }

    public function test_that_login_deletes_the_session(): void
    {
        $this->client->request('POST', '/login', [
            'json' => [
                'type' => 'ldap',
                'username' => '10000',
                'password' => 'secret',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'user' => [
                'profile' => [
                    'firstName' => 'Max',
                    'lastName' => 'Mustermann'
                ]
            ],
        ]);

        $this->client->request('GET', '/test');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'message' => 'You are logged in as max.mustermann@awesome-university.com',
        ]);

        $this->client->request('POST', '/logout');
        $this->assertResponseStatusCodeSame(200);

        $this->client->request('GET', '/test');

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains(['code' => ResponseCodes::$UNAUTHENTICATED]);
    }
}
