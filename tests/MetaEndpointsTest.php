<?php

class MetaEndpointsTest extends TestCase
{
    /**
     * Test the /meta endpoint.
     */
    public function testMeta()
    {
        $this->get('/api/v1/meta')
            ->seeJson([
                'id'                => 'proxmox',
                'name'              => 'Proxmox',
                'server_nickname'   => 'VM',
                'default_region'    => 'own',
                'default_size'      => '512mb',
                'can_reboot'        => true,
                'can_rename'        => true,
                'ssh_auth_method'   => 'password',
                'credential_fields' => [
                    'host',
                    'user',
                    'realm',
                    'password',
                ],
            ]);
    }

    /**
     * Test the /catalog endpoint.
     */
    public function testCatalog()
    {
        $this->get('/api/v1/catalog')
            ->seeJson(['id' => 'own', 'name' => 'Self-Owned/Operated'])
            ->seeJson(['title' => 'Single Core', 'specs' => [
                ['id' => '512mb', 'ram' => 512, 'cpu' => 1, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                ['id' => '1gb', 'ram' => 1024, 'cpu' => 1, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
            ]])
            ->seeJson(['title' => 'Dual Core', 'specs' => [
                ['id' => '512mb2c', 'ram' => 512, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                ['id' => '1gb2c', 'ram' => 1024, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                ['id' => '2gb2c', 'ram' => 2048, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
            ]])
            ->seeJson(['title' => 'Quad Core', 'specs' => [
                ['id' => '1gb4c', 'ram' => 1024, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                ['id' => '2gb4c', 'ram' => 2048, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                ['id' => '4gb4c', 'ram' => 4096, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
            ]])
            ->seeJson(['title' => 'Quad Core High Capacity', 'specs' => [
                ['id' => '1gb4c+', 'ram' => 1024, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                ['id' => '2gb4c+', 'ram' => 2048, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                ['id' => '4gb4c+', 'ram' => 4096, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
            ]]);
    }

    /**
     * Test the /verify endpoint.
     */
    public function testVerify()
    {
        $this->json('POST', '/api/v1/verify')
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/verify', ['auth' => ['host' => '', 'user' => '', 'realm' => '']])
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/verify', ['auth' => ['host' => 'string', 'user' => 'string', 'realm' => 'string']])
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/verify', ['auth' => ['host' => '', 'user' => '', 'realm' => '', 'password' => '']])
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/verify', ['auth' => ['host' => 'string', 'user' => 'string', 'realm' => 'string', 'password' => 'string']])
            ->see('');
    }
}
