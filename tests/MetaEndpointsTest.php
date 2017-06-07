<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\Mock;

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
                'server_nick_name',
                'default_region',
                'default_size',
                'default_plan',
                'can_reboot',
                'can_rename',
                'internal_iface',
                'external_iface',
                'ssh_user',
                'ssh_auth_method',
                'ssh_key_method',
                'bootstrap_script',
                'credential_fields' => [
                    '*' => [
                        'key',
                        'label',
                    ],
                ],
                'instructions',
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
                            'id',
                            'name',
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
        Proxmox::swap(new Mock());
        Proxmox::shouldReceive('login')->once()->andReturn(null);
        Proxmox::shouldReceive('get')->once()->with('/nodes')->andReturn(['data' => [['node' => 'pve']]]);
        Proxmox::shouldReceive('get')->once()->with('/storage')->andReturn(['data' => [['storage' => 'local-lvm']]]);

        $this->json('POST', '/api/v1/verify')
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('POST', '/api/v1/verify', [], [
            'auth-hostname' => '',
            'auth-username' => '',
            'auth-realm'    => '',
        ])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('POST', '/api/v1/verify', [], [
            'auth-hostname' => $user->hostname,
            'auth-username' => $user->username,
            'auth-realm'    => $user->realm,
        ])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('POST', '/api/v1/verify', [], [
            'auth-hostname' => '',
            'auth-username' => '',
            'auth-realm'    => '',
            'auth-password' => '',
        ])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('POST', '/api/v1/verify', [], [
            'auth-hostname' => $user->hostname,
            'auth-username' => $user->username,
            'auth-realm'    => $user->realm,
            'auth-password' => $user->password,
        ])
            ->assertResponseStatus(200)
            ->see('');

        $this->json('POST', '/api/v1/verify', [], [
            'auth-hostname' => $user->hostname,
            'auth-port'     => $user->port,
            'auth-username' => $user->username,
            'auth-realm'    => $user->realm,
            'auth-password' => $user->password,
            'auth-node'     => 'pve',
            'auth-storage'  => 'local-lvm',
        ])
            ->assertResponseStatus(200)
            ->see('');
    }
}
