<?php

use App\Server;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ServerEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test the POST /servers endpoint.
     */
    public function testServerCreate()
    {
        $this->beginDatabaseTransaction();

        $owner = factory(User::class)->create();

        $this->json('POST', '/api/v1/servers')
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/servers', ['name' => 'test', 'region' => 'own', 'size' => '512mb'])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/servers', ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'password' => $owner->password], 'name' => 'test', 'region' => 'own', 'size' => '512mb'])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('POST', '/api/v1/servers', ['auth' => ['host' => 'string', 'user' => 'string', 'realm' => 'string', 'password' => 'string'], 'name' => 'test', 'region' => 'own', 'size' => '512mb'])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('POST', '/api/v1/servers', ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'realm' => $owner->realm, 'password' => $owner->password], 'name' => 'test1', 'region' => 'own', 'size' => '512mb'])
            ->assertResponseStatus(201)
            ->seeJsonStructure([
                'id',
            ]);
        $this->seeInDatabase('servers', ['name' => 'test1']);

        $this->json('POST', '/api/v1/servers', ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'realm' => $owner->realm, 'password' => $owner->password], 'name' => 'test2'])
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
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'password' => $owner->password], 'id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['auth' => ['host' => 'string', 'user' => 'string', 'realm' => 'string', 'password' => 'string'], 'id' => $server->unique_id])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['auth' => ['host' => $impostor->host, 'user' => $impostor->user, 'realm' => $impostor->realm, 'password' => $impostor->password], 'id' => $server->unique_id])
            ->assertResponseStatus(403)
            ->seeJson(['errors' => ['Server belongs to different user']]);

        $this->json('GET', "/api/v1/servers/{$server->unique_id}", ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'realm' => $owner->realm, 'password' => $owner->password], 'id' => $server->unique_id])
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

        $owner    = factory(User::class)->create();
        $impostor = factory(User::class)->create();
        $server   = factory(Server::class)->create([
            'user_id' => $owner->id,
        ]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}")
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'password' => $owner->password], 'id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['auth' => ['host' => 'string', 'user' => 'string', 'realm' => 'string', 'password' => 'string'], 'id' => $server->unique_id])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['auth' => ['host' => $impostor->host, 'user' => $impostor->user, 'realm' => $impostor->realm, 'password' => $impostor->password], 'id' => $server->unique_id])
            ->assertResponseStatus(403)
            ->seeJson(['errors' => ['Server belongs to different user']]);

        $this->json('DELETE', "/api/v1/servers/{$server->unique_id}", ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'realm' => $owner->realm, 'password' => $owner->password], 'id' => $server->unique_id])
            ->assertResponseStatus(200)
            ->see('');
        $this->assertNotNull(Server::withTrashed()->where('id', $server->id)->first()->deleted_at);
    }

    /**
     * Test the /servers/{id}/reboot endpoint.
     */
    public function testServerReboot()
    {
        $this->beginDatabaseTransaction();

        $owner    = factory(User::class)->create();
        $impostor = factory(User::class)->create();
        $server   = factory(Server::class)->create([
            'user_id' => $owner->id,
        ]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot")
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'password' => $owner->password], 'id' => $server->unique_id])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['auth' => ['host' => 'string', 'user' => 'string', 'realm' => 'string', 'password' => 'string'], 'id' => $server->unique_id])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['auth' => ['host' => $impostor->host, 'user' => $impostor->user, 'realm' => $impostor->realm, 'password' => $impostor->password], 'id' => $server->unique_id])
            ->assertResponseStatus(403)
            ->seeJson(['errors' => ['Server belongs to different user']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/reboot", ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'realm' => $owner->realm, 'password' => $owner->password], 'id' => $server->unique_id])
            ->assertResponseStatus(200)
            ->see('');
    }

    /**
     * Test the /servers/{id}/rename endpoint.
     */
    public function testServerRename()
    {
        $this->beginDatabaseTransaction();

        $owner    = factory(User::class)->create();
        $impostor = factory(User::class)->create();
        $server   = factory(Server::class)->create([
            'user_id' => $owner->id,
        ]);
        $faker   = Faker\Factory::create();
        $newName = $faker->domainName;

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename")
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['id' => $server->unique_id, 'name' => $faker->domainName])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'password' => $owner->password], 'id' => $server->unique_id, 'name' => $faker->domainName])
            ->assertResponseStatus(401)
            ->seeJson(['errors' => ['Missing one or more creds']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['auth' => ['host' => 'string', 'user' => 'string', 'realm' => 'string', 'password' => 'string'], 'id' => $server->unique_id, 'name' => $faker->domainName])
            ->assertResponseStatus(404)
            ->seeJson(['errors' => ['Invalid User']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['auth' => ['host' => $impostor->host, 'user' => $impostor->user, 'realm' => $impostor->realm, 'password' => $impostor->password], 'id' => $server->unique_id, 'name' => $newName])
            ->assertResponseStatus(403)
            ->seeJson(['errors' => ['Server belongs to different user']]);

        $this->json('PATCH', "/api/v1/servers/{$server->unique_id}/rename", ['auth' => ['host' => $owner->host, 'user' => $owner->user, 'realm' => $owner->realm, 'password' => $owner->password], 'id' => $server->unique_id, 'name' => $newName])
            ->assertResponseStatus(200)
            ->see('');
        $this->assertEquals($server->fresh()->name, $newName);
    }
}
