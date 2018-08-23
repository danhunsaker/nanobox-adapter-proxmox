<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Jobs\DeleteVM;
use App\Jobs\RebootVM;
use App\Jobs\RenameVM;
use App\Jobs\TrackVMCreate;
use App\Key;
use App\Region;
use App\Server;
use App\ServerSize;
use App\User;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Proxmox;
use ProxmoxVE\Exception\AuthenticationException;
use ProxmoxVE\Exception\MalformedCredentialsException;

class ServerController extends Controller
{
    /**
     *  @SWG\Post(
     *      path="/servers",
     *      summary="Order Server",
     *      tags={"servers"},
     *      operationId="order-server",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#order-server",
     *      },
     *      description="The `/servers` route is how Nanobox submits a request to order a new server. This route SHOULD NOT hold open the request until the server is ready. The request should return immediately once the order has been submitted with an identifier that Nanobox can use to followup on the order status.",
     *      @SWG\Parameter(ref="#/parameters/Auth-Hostname"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Port"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Username"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Realm"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Password"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Node"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Storage"),
     *      @SWG\Parameter(
     *          name="payload",
     *          description="Server creation data",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"name","region","size"},
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="Nanobox-generated name used to identify the machine visually as ordered by Nanobox",
     *              ),
     *              @SWG\Property(
     *                  property="region",
     *                  type="string",
     *                  description="the region wherein to launch the server, which will match the region `id` from the catalog",
     *              ),
     *              @SWG\Property(
     *                  property="size",
     *                  type="string",
     *                  description="the size of server to provision, which will match an `id` provided in the aforementioned catalog",
     *              ),
     *              @SWG\Property(
     *                  property="ssh_key",
     *                  type="string",
     *                  description="id of the SSH key created during the `/keys` request",
     *              ),
     *          ),
     *      ),
     *      @SWG\Response(
     *          response="default",
     *          ref="#/responses/default",
     *      ),
     *      @SWG\Response(
     *          response=201,
     *          description="successful operation",
     *          examples={
     *              "application/json"={
     *                  "id"="provider-server-ID",
     *              },
     *          },
     *          @SWG\Schema(
     *              type="object",
     *              required={"id"},
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="unique id of the server",
     *              ),
     *          ),
     *      ),
     *  )
     */
    public function store(Request $request)
    {
        $user   = $request->user;
        $region = Region::where('code', $request->json('region', Region::first()->code))->firstOrFail();
        $size   = ServerSize::where('code', $request->json('size', ServerSize::first()->code))->firstOrFail();
        $key    = Key::where('name', $request->json('ssh_key'))->first();

        $name     = $request->json('name');
        $password = Str::random(25);
        $node     = $request->creds['node'];
        $storage  = $request->creds['storage'];
        $archive  = "backup/vzdump-qemu-nanobox-ubuntu-{$size->disk}G.vma.gz";
        $server   = new Server(compact('name', 'password', 'node', 'storage'));

        $server->user()->associate($user);
        $server->region()->associate($region);
        $server->serverSize()->associate($size);

        if ( ! is_null($key)) {
            $server->key()->associate($key);
            $server->password = null;
        }

        $this->getHost($user);
        $server->vmid = Proxmox::get('/cluster/nextid')['data'];

        // Verify the image we want is actually available
        $result = Proxmox::get("/nodes/{$node}/storage/local/content/{$archive}");

        if (isset($result['errors'])) {
            abort(502, json_encode($result['errors']));
        } elseif (empty($result['data'])) {
            abort(502, "local:{$archive} image not found on server");
        }

        // Looks alright; create the VM
        $result = Proxmox::create("/nodes/{$node}/qemu", [
            'vmid'    => $server->vmid,
            'storage' => $storage,
            'archive' => "local:{$archive}",
            'sshkeys' => $key->key,
            'unique'  => true,
        ]);

        if (isset($result['errors'])) {
            abort(502, json_encode($result['errors']));
        } elseif (empty($result['data'])) {
            abort(502, 'Unknown error on Proxmox server');
        }

        // Success!  Save the server details to the DB
        $server->unique_id = Str::slug(strtr("proxmox {$region->code} {$user->hostname} {$server->vmid}", '.', '_'));

        $server->save();

        $this->dispatch(new TrackVMCreate($server, $result['data']));

        return response()->json(['id' => $server->unique_id], 201);
    }

    /**
     * @SWG\Get(
     *      path="/servers/{id}",
     *      summary="Query Server",
     *      tags={"servers"},
     *      operationId="query-server",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#query-server",
     *      },
     *      description="The `GET /servers/{id}` route is used by Nanobox to query state about a previously ordered server. This state is used to inform Nanobox when the server is ready to be provisioned and also how to connect to the server.",
     *      @SWG\Parameter(ref="#/parameters/Auth-Hostname"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Port"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Username"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Realm"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Password"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Node"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Storage"),
     *      @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          description="the server id",
     *          required=true,
     *          type="string",
     *      ),
     *      @SWG\Response(
     *          response="default",
     *          ref="#/responses/default",
     *      ),
     *      @SWG\Response(
     *          response=201,
     *          description="successful operation",
     *          examples={
     *              "application/json"={
     *                  "id"="provider-server-ID",
     *                  "status"="active",
     *                  "name"="nanobox.io-cool-app-do.1.1",
     *                  "external_ip"="192.0.2.15",
     *                  "internal_ip"="192.168.0.15",
     *                  "password"="d7h%*ttGqY"
     *              },
     *          },
     *          @SWG\Schema(
     *              type="object",
     *              required={"id","status","name","external_ip","internal_ip"},
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="the server id",
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="string",
     *                  description="the status or availability of the server. (active indicates server is ready)",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="name of the server",
     *              ),
     *              @SWG\Property(
     *                  property="external_ip",
     *                  type="string",
     *                  description="external or public IP of the server",
     *              ),
     *              @SWG\Property(
     *                  property="internal_ip",
     *                  type="string",
     *                  description="internal or private IP of the server",
     *              ),
     *              @SWG\Property(
     *                  property="password",
     *                  type="string",
     *                  description="the ssh password to use (if ssh_auth_method is password)",
     *              ),
     *          ),
     *      ),
     * )
     */
    public function show(Request $request, $id)
    {
        $user   = $request->user;
        $server = Server::where('unique_id', $id)->firstOrFail();

        if ($server->user->id != $user->id) {
            return abort(403, 'Server belongs to different user');
        }

        $result = [
            'id'          => $server->unique_id,
            'status'      => $server->status,
            'name'        => $server->name,
            'external_ip' => $server->external_ip,
            'internal_ip' => $server->internal_ip,
        ];

        if ( ! empty($server->password)) {
            $result['password'] = $server->password;
        }

        return response()->json($result, 201);
    }

    /**
     * @SWG\Delete(
     *      path="/servers/{id}",
     *      summary="Cancel Server",
     *      tags={"servers"},
     *      operationId="cancel-server",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#cancel-server",
     *      },
     *      description="The `DELETE /servers/{id}` route is used to cancel a server that was previously ordered via Nanobox. This route SHOULD NOT hold open the request until the server is completely canceled. It should return immediately once the order to cancel has been submitted.",
     *      @SWG\Parameter(ref="#/parameters/Auth-Hostname"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Port"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Username"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Realm"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Password"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Node"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Storage"),
     *      @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          description="the server id",
     *          required=true,
     *          type="string",
     *      ),
     *      @SWG\Response(
     *          response="default",
     *          ref="#/responses/default",
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *      ),
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user   = $request->user;
        $server = Server::where('unique_id', $id)->firstOrFail();

        if ($server->user->id != $user->id) {
            return abort(403, 'Server belongs to different user');
        }

        $server->status = 'destroying';
        $server->save();

        $this->getHost($user);
        $result = Proxmox::create("/nodes/{$server->node}/qemu/{$server->vmid}/status/stop");
        $this->handleErrors($result, $server);
        $this->dispatch(new DeleteVM($server, $result['data']));

        return response('', 200);
    }

    /**
     * @SWG\Patch(
     *      path="/servers/{id}/reboot",
     *      summary="Reboot Server",
     *      tags={"servers"},
     *      operationId="reboot-server",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#reboot-server",
     *      },
     *      description="The `/servers/{id}/reboot` route is used to reboot a server that was previously ordered via Nanobox. This route SHOULD NOT hold open the request until the server is completely rebooted. It should return immediately once the order to reboot has been submitted",
     *      @SWG\Parameter(ref="#/parameters/Auth-Hostname"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Port"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Username"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Realm"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Password"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Node"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Storage"),
     *      @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          description="the server id",
     *          required=true,
     *          type="string",
     *      ),
     *      @SWG\Response(
     *          response="default",
     *          ref="#/responses/default",
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *      ),
     * )
     */
    public function reboot(Request $request, $id)
    {
        $user   = $request->user;
        $server = Server::where('unique_id', $id)->firstOrFail();

        if ($server->user->id != $user->id) {
            return abort(403, 'Server belongs to different user');
        }

        $server->status = 'rebooting';
        $server->save();

        $this->getHost($user);
        $result = Proxmox::create("/nodes/{$server->node}/qemu/{$server->vmid}/status/shutdown");
        $this->handleErrors($result, $server);
        $this->dispatch(new RebootVM($server, $result['data']));

        return response('', 200);
    }

    /**
     * @SWG\Patch(
     *      path="/servers/{id}/rename",
     *      summary="Rename Server",
     *      tags={"servers"},
     *      operationId="rename-server",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#rename-server",
     *      },
     *      description="The `/servers/{id}/rename` route is used to rename a server that was previously ordered via Nanobox. This route SHOULD NOT hold open the request until the server is completely renamed. It should return immediately once the order to rename has been submitted.",
     *      @SWG\Parameter(ref="#/parameters/Auth-Hostname"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Port"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Username"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Realm"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Password"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Node"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Storage"),
     *      @SWG\Parameter(
     *          name="payload",
     *          description="New server name",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"name"},
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="the new name of the server",
     *              ),
     *          ),
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          description="the server id",
     *          required=true,
     *          type="string",
     *      ),
     *      @SWG\Response(
     *          response="default",
     *          ref="#/responses/default",
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *      ),
     * )
     */
    public function rename(Request $request, $id)
    {
        $user   = $request->user;
        $server = Server::where('unique_id', $id)->firstOrFail();
        $name   = $request->json('name');

        if ($server->user->id != $user->id) {
            return abort(403, 'Server belongs to different user');
        }

        $this->getHost($user);
        $result = Proxmox::create("/nodes/{$server->node}/qemu/{$server->vmid}/config", compact('name'));
        $this->handleErrors($result, $server);
        $this->dispatch(new RenameVM($server, $result['data'], $name));

        return response('', 200);
    }

    protected function getHost($user)
    {
        try {
            config(['proxmox.server' => $user->makeVisible('password')->toArray()]);
            Proxmox::login();
        } catch (RequestException $e) {
            abort(400, $e->getMessage());
        } catch (MalformedCredentialsException $e) {
            abort(400, $e->getMessage());
        } catch (AuthenticationException $e) {
            abort(403, $e->getMessage());
        }
    }

    protected function handleErrors($result, $server)
    {
        if (isset($result['errors'])) {
            $server->status = 'error';
            $server->save();
            abort(502, json_encode($result['errors']));
        } elseif (empty($result['data'])) {
            $server->status = 'error';
            $server->save();
            abort(502, 'Unknown error on Proxmox server');
        }
    }
}
