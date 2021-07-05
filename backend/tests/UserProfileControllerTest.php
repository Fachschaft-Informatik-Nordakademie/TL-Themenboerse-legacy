<?php

namespace App\Tests;

class UserProfileControllerTest extends SecureApiTestCase
{
    public function test_that_topic_creation_works(): void
    {
        $this->ensureLogin();

        $this->client->request('PUT', '/user_profile', [
            'json' => [
                'firstName' => 'First',
                'lastName' => 'Last',
                'biography' => 'Some biography',
                'company' => 'Awesome Company AG',
                'job' => 'Developer',
                'courseOfStudy' => 'Computer science',
                'skills' => array('none'),
                'references' => array('none')
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function test_topic_retrieved_with_id(): void
    {
        $this->ensureLogin();

        $resp = $this->client->request('PUT', '/user_profile', [
            'json' => [
                'firstName' => 'First',
                'lastName' => 'Last',
                'biography' => 'Some biography',
                'company' => 'Awesome Company AG',
                'job' => 'Developer',
                'courseOfStudy' => 'Computer science',
                'skills' => ['none'],
                'references' => ['none']
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $id = json_decode($resp->getContent(), true)['id'];

        $this->client->request('GET', '/user_profile/' . $id);
        $this->assertJsonContains([
            'firstName' => 'First',
            'lastName' => 'Last',
            'biography' => 'Some biography',
            'company' => 'Awesome Company AG',
            'job' => 'Developer',
            'courseOfStudy' => 'Computer science',
            'skills' => ['none'],
            'references' => ['none']
        ]);
    }

    public function test_topic_empty_request_fails(): void
    {
        $this->ensureLogin();
        $this->client->request('PUT', '/user_profile');
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_topic_malformed_request_data(): void
    {
        $this->ensureLogin();
        $resp = $this->client->request('PUT', '/user_profile', [
            'json' => [
                'firstName' => '',
                'lastName' => ''
            ],
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_topic_unauthorized_access(): void
    {
        $this->ensureLogout();
        $this->client->request('PUT', '/user_profile');
        $this->assertResponseStatusCodeSame(401);
    }

    public function test_topic_fails_retrieve(): void
    {
        $this->ensureLogin();
        $this->client->request('GET', '/user_profile/9999999');
        $this->assertResponseStatusCodeSame(404);
    }
}
