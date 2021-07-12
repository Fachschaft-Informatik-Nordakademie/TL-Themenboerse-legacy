<?php

namespace App\Test;

use App\Entity\User;
use App\Tests\SecureApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminUserControllerTest extends SecureApiTestCase
{

    public function test_that_normal_users_cannot_access_admin_page(): void
    {
        $this->ensureLogin();

        $this->client->request('PUT', '/admin/user/3',  [
            'json' => [
                'admin' => true
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function test_that_admin_user_can_make_another_user_admin(): void
    {
        $this->em->createQueryBuilder()
            ->update(User::class, 'u')
            ->set('u.admin', true)
            ->where('u.email = :email')
            ->setParameter('email', 'dummy@example.com')
            ->getQuery()
            ->execute();

        $this->ensureLogin();

        $this->client->request('PUT', '/admin/user/3',  [
            'json' => [
                'admin' => true
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        /** @var User $user */
        $user = $this->em->find(User::class, 3);
        $this->assertTrue($user->isAdmin());
    }
}