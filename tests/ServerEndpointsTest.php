<?php

use App\Server;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\Mock;

class ServerEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test the POST /servers endpoint.
     */
    public function testServerCreate()
    {
        $this->beginDatabaseTransaction();
        $this->expectsJobs(App\Jobs\TrackVMCreate::class);

        $owner = factory(User::class)->create();
        Proxmox::swap(new Mock());
        Proxmox::shouldReceive('login')->once()->andReturn(null);
        Proxmox::shouldReceive('get')->once()->with('/cluster/nextid')->andReturn(['data' => 9999]);
        Proxmox::shouldReceive('get')->once()->with('/nodes/pve/storage/local/content/backup/vzdump-qemu-nanobox-ubuntu-40G.vma.gz')->andReturn(['data' => true]);
        Proxmox::shouldReceive('create')->once()->with('/nodes/pve/qemu', ['vmid' => 9999, 'storage' => 'local-lvm', 'archive' => 'local:backup/vzdump-qemu-nanobox-ubuntu-40G.vma.gz', 'unique' => 'true'])->andReturn(['data' => 'TASK:ID']);

        $this->json('POST', '/api/v1/servers')
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('POST', '/api/v1/servers', ['name' => 'test', 'region' => 'own', 'size' => '512mb'])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('POST', '/api/v1/servers', ['name' => 'test', 'region' => 'own', 'size' => '512mb'], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-password' => $owner->password])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('POST', '/api/v1/servers', ['name' => 'test', 'region' => 'own', 'size' => '512mb'], ['auth-hostname' => 'string', 'auth-username' => 'string', 'auth-realm' => 'string', 'auth-password' => 'string'])
            ->seeJson(['errors' => ['Invalid User']])
            ->assertResponseStatus(404);

        $this->json('POST', '/api/v1/servers', ['name' => 'test1', 'region' => 'own', 'size' => '512mb'], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-realm' => $owner->realm, 'auth-password' => $owner->password])
            ->assertResponseStatus(201)
            ->seeJsonStructure([
                'id',
            ]);
        $this->seeInDatabase('servers', ['name' => 'test1']);

        $this->json('POST', '/api/v1/servers', ['name' => 'test2'], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-realm' => $owner->realm, 'auth-password' => $owner->password])
            ->assertResponseStatus(201)
            ->seeJsonStructure([
                'id',
            ]);
        $this->seeInDatabase('servers', ['name' => 'test2']);
    }

    /**
     * Test the GET /servers/{id} endpoint.
     */
    public function testServerQuery()
    {
        $this->beginDatabaseTransaction();

        $owner    = factory(User::class)->create();
        $impostor = factory(User::class)->create();
        $server   = factory(Server::class)->create([
            'user_id' => $owner->id,
        ]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}")
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-password' => $owner->password])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id], ['auth-hostname' => 'string', 'auth-username' => 'string', 'auth-realm' => 'string', 'auth-password' => 'string'])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id], ['auth-hostname' => $impostor->hostname, 'auth-username' => $impostor->username, 'auth-realm' => $impostor->realm, 'auth-password' => $impostor->password])
            ->assertResponseStatus(403)
            ->seeJson(['errors' => ['Server belongs to different user']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-realm' => $owner->realm, 'auth-password' => $owner->password])
            ->assertResponseStatus(201)
            ->seeJsonStructure([
                'id',
                'status',
                'name',
                'external_ip',
                'internal_ip',
            ]);
    }

    /**
     * Test the DELETE /servers/{id} endpoint.
     */
    public function testServerCancel()
    {
        $this->beginDatabaseTransaction();
        $this->expectsJobs(App\Jobs\DeleteVM::class);

        $owner    = factory(User::class)->create();
        $impostor = factory(User::class)->create();
        $server   = factory(Server::class)->create([
            'user_id' => $owner->id,
        ]);
        Proxmox::swap(new Mock());
        Proxmox::shouldReceive('login')->once()->andReturn(null);
        Proxmox::shouldReceive('create')->once()->with("/nodes/pve/qemu/{$server->vmid}/status/stop")->andReturn(['data' => 'TASK:ID']);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}")
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-password' => $owner->password])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id], ['auth-hostname' => 'string', 'auth-username' => 'string', 'auth-realm' => 'string', 'auth-password' => 'string'])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id], ['auth-hostname' => $impostor->hostname, 'auth-username' => $impostor->username, 'auth-realm' => $impostor->realm, 'auth-password' => $impostor->password])
            ->assertResponseStatus(403)
            ->seeJson(['errors' => ['Server belongs to different user']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-realm' => $owner->realm, 'auth-password' => $owner->password])
            ->assertResponseStatus(200)
            ->see('');
    }

    /**
     * Test the /servers/{id}/reboot endpoint.
     */
    public function testServerReboot()
    {
        $this->beginDatabaseTransaction();
        $this->expectsJobs(App\Jobs\RebootVM::class);

        $owner    = factory(User::class)->create();
        $impostor = factory(User::class)->create();
        $server   = factory(Server::class)->create([
            'user_id' => $owner->id,
        ]);
        Proxmox::swap(new Mock());
        Proxmox::shouldReceive('login')->once()->andReturn(null);
        Proxmox::shouldReceive('create')->once()->with("/nodes/pve/qemu/{$server->vmid}/status/shutdown")->andReturn(['data' => 'TASK:ID']);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot")
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['id' => $server->unique_id], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-password' => $owner->password])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['id' => $server->unique_id], ['auth-hostname' => 'string', 'auth-username' => 'string', 'auth-realm' => 'string', 'auth-password' => 'string'])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['id' => $server->unique_id], ['auth-hostname' => $impostor->hostname, 'auth-username' => $impostor->username, 'auth-realm' => $impostor->realm, 'auth-password' => $impostor->password])
            ->assertResponseStatus(403)
            ->seeJson(['errors' => ['Server belongs to different user']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['id' => $server->unique_id], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-realm' => $owner->realm, 'auth-password' => $owner->password])
            ->assertResponseStatus(200)
            ->see('');
    }

    /**
     * Test the /servers/{id}/rename endpoint.
     */
    public function testServerRename()
    {
        $this->beginDatabaseTransaction();
        $this->expectsJobs(App\Jobs\RenameVM::class);

        $owner    = factory(User::class)->create();
        $impostor = factory(User::class)->create();
        $server   = factory(Server::class)->create([
            'user_id' => $owner->id,
        ]);
        $faker   = Faker\Factory::create();
        $newName = $faker->domainName;
        Proxmox::swap(new Mock());
        Proxmox::shouldReceive('login')->once()->andReturn(null);
        Proxmox::shouldReceive('create')->once()->with("/nodes/pve/qemu/{$server->vmid}/config", ['name' => $newName])->andReturn(['data' => 'TASK:ID']);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename")
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['id' => $server->unique_id, 'name' => $faker->domainName])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['id' => $server->unique_id, 'name' => $faker->domainName], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-password' => $owner->password])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more required creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['id' => $server->unique_id, 'name' => $faker->domainName], ['auth-hostname' => 'string', 'auth-username' => 'string', 'auth-realm' => 'string', 'auth-password' => 'string'])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['id' => $server->unique_id, 'name' => $newName], ['auth-hostname' => $impostor->hostname, 'auth-username' => $impostor->username, 'auth-realm' => $impostor->realm, 'auth-password' => $impostor->password])
            ->assertResponseStatus(403)
            ->seeJson(['errors' => ['Server belongs to different user']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['id' => $server->unique_id, 'name' => $newName], ['auth-hostname' => $owner->hostname, 'auth-username' => $owner->username, 'auth-realm' => $owner->realm, 'auth-password' => $owner->password])
            ->assertResponseStatus(200)
            ->see('');
    }
}
