<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MetaEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test the /meta endpoint.
     */
    public function testMeta()
    {
        $this->get('/api/v1/meta')
            ->assertResponseStatus(200)
            ->seeJsonStructure([
                'id',
                'name',
                'server_nickname',
                'default_region',
                'default_size',
                'can_reboot',
                'can_rename',
                'ssh_auth_method',
                'credential_fields',
            ]);
    }

    /**
     * Test the /catalog endpoint.
     */
    public function testCatalog()
    {
        $this->get('/api/v1/catalog')
            ->assertResponseStatus(200)
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'plans' => [
                        '*' => [
                            'title',
                            'specs' => [
                                '*' => [
                                    'id',
                                    'ram',
                                    'cpu',
                                    'disk',
                                    'transfer',
                                    'dollars_per_hr',
                                    'dollars_per_mo',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test the /verify endpoint.
     */
    public function testVerify()
    {
        $this->beginDatabaseTransaction();

        $user = factory(User::class)->create();

        $this->json('POST', '/api/v1/verify')
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/verify', ['auth' => ['host' => '', 'user' => '', 'realm' => '']])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/verify', ['auth' => ['host' => $user->host, 'user' => $user->user, 'realm' => $user->realm]])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/verify', ['auth' => ['host' => '', 'user' => '', 'realm' => '', 'password' => '']])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/verify', ['auth' => ['host' => $user->host, 'user' => $user->user, 'realm' => $user->realm, 'password' => $user->password]])
            ->assertResponseStatus(200)
            ->see('');
    }
}
