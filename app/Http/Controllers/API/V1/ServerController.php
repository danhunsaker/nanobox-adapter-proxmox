<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Key;
use App\Region;
use App\Server;
use App\ServerSize;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServerController extends Controller
{
    /**
     *  @SWG\Post(
     *      path="/servers",
     *      summary="Order Server",
     *      tags={"servers"},
     *      description="The `/servers` route is how nanobox submits a request to order a new server. This route SHOULD NOT hold open the request until the server is ready. The request should return immediately once the order has been submitted with an identifier that nanobox can use to followup on the order status.",
     *      @SWG\Parameter(
     *          name="payload",
     *          description="User creds and server creation data",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"auth","name","region","size"},
     *              @SWG\Property(
     *                  property="auth",
     *                  type="object",
     *                  description="key/value pairs containing the `credential_fields` and their corresponding values as populated by the user. This will provide the necessary values to authorize the user within this provider",
     *                  required={"host","user","realm","password"},
     *                  @SWG\Property(
     *                      property="host",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="user",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="realm",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="password",
     *                      type="string",
     *                  ),
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="nanobox-generated name used to identify the machine visually as ordered by nanobox",
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
     *          response=201,
     *          description="successful operation",
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
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @SWG\Schema(
     *              type="object",
     *              required={"errors"},
     *              @SWG\Property(
     *                  property="errors",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     *  )
     */
    public function store(Request $request)
    {
        $user   = User::where(collect($request->json('auth'))->only(['host', 'user', 'realm'])->all())->first();
        $region = Region::where('code', $request->json('region', 'own'))->firstOrFail();
        $size   = ServerSize::where('code', $request->json('size', '512mb'))->firstOrFail();
        $key    = Key::where('name', $request->json('ssh_key'))->first();

        $name   = $request->json('name');
        $server = Server::create(compact('name'));

        $server->user()->associate($user);
        $server->region()->associate($region);
        $server->serverSize()->associate($size);

        if ( ! is_null($key)) {
            $server->key()->associate($key);
        }

        $server->password = Str::random(25);

        // TODO: Create server

        // Need to update this to use the VMID instead of this API's internal ID
        $server->unique_id = Str::slug("{$region->code} {$user->host} {$server->id}");

        $server->save();

        return response()->json(['id' => $server->unique_id], 201);
    }

    /**
     * @SWG\Get(
     *      path="/servers/{id}",
     *      summary="Query Server",
     *      tags={"servers"},
     *      description="The `GET /servers/{id}` route is used by nanobox to query state about a previously ordered server. This state is used to inform nanobox when the server is ready to be provisioned and also how to connect to the server.",
     *      @SWG\Parameter(
     *          name="payload",
     *          description="User creds and server ID",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"auth"},
     *              @SWG\Property(
     *                  property="auth",
     *                  type="object",
     *                  description="key/value pairs containing the `credential_fields` and their corresponding values as populated by the user. This will provide the necessary values to authorize the user within this provider",
     *                  required={"host","user","realm","password"},
     *                  @SWG\Property(
     *                      property="host",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="user",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="realm",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="password",
     *                      type="string",
     *                  ),
     *              ),
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="the server id (added here as a convenience)",
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
     *          response=201,
     *          description="successful operation",
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
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @SWG\Schema(
     *              type="object",
     *              required={"errors"},
     *              @SWG\Property(
     *                  property="errors",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     * )
     */
    public function show(Request $request, $id)
    {
        $user   = User::where(collect($request->json('auth'))->only(['host', 'user', 'realm'])->all())->first();
        $server = Server::where('unique_id', $id)->firstOrFail();

        if ($server->user->id != $user->id) {
            return abort(403, 'Server belongs to different user');
        }

        return response()->json([
            'id'          => $server->unique_id,
            'status'      => $server->status,
            'name'        => $server->name,
            'external_ip' => $server->external_ip,
            'internal_ip' => $server->internal_ip,
            'password'    => $server->password,
        ], 201);
    }

    /**
     * @SWG\Delete(
     *      path="/servers/{id}",
     *      summary="Cancel Server",
     *      tags={"servers"},
     *      description="The `DELETE /servers/{id}` route is used to cancel a server that was previously ordered via nanobox. This route SHOULD NOT hold open the request until the server is completely canceled. It should return immediately once the order to cancel has been submitted.",
     *      @SWG\Parameter(
     *          name="payload",
     *          description="User creds and server ID",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"auth"},
     *              @SWG\Property(
     *                  property="auth",
     *                  type="object",
     *                  description="key/value pairs containing the `credential_fields` and their corresponding values as populated by the user. This will provide the necessary values to authorize the user within this provider",
     *                  required={"host","user","realm","password"},
     *                  @SWG\Property(
     *                      property="host",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="user",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="realm",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="password",
     *                      type="string",
     *                  ),
     *              ),
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="the server id (added here as a convenience)",
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
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @SWG\Schema(
     *              type="object",
     *              required={"errors"},
     *              @SWG\Property(
     *                  property="errors",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user   = User::where(collect($request->json('auth'))->only(['host', 'user', 'realm'])->all())->first();
        $server = Server::where('unique_id', $id)->firstOrFail();

        if ($server->user->id != $user->id) {
            return abort(403, 'Server belongs to different user');
        }

        // TODO: Destroy server

        $server->delete();

        return response('', 200);
    }

    /**
     * @SWG\Patch(
     *      path="/servers/{id}/reboot",
     *      summary="Reboot Server",
     *      tags={"servers"},
     *      description="The `/servers/{id}/reboot` route is used to reboot a server that was previously ordered via nanobox. This route SHOULD NOT hold open the request until the server is completely rebooted. It should return immediately once the order to reboot has been submitted",
     *      @SWG\Parameter(
     *          name="payload",
     *          description="User creds and server ID",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"auth"},
     *              @SWG\Property(
     *                  property="auth",
     *                  type="object",
     *                  description="key/value pairs containing the `credential_fields` and their corresponding values as populated by the user. This will provide the necessary values to authorize the user within this provider",
     *                  required={"host","user","realm","password"},
     *                  @SWG\Property(
     *                      property="host",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="user",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="realm",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="password",
     *                      type="string",
     *                  ),
     *              ),
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="the server id (added here as a convenience)",
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
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @SWG\Schema(
     *              type="object",
     *              required={"errors"},
     *              @SWG\Property(
     *                  property="errors",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     * )
     */
    public function reboot(Request $request, $id)
    {
        $user   = User::where(collect($request->json('auth'))->only(['host', 'user', 'realm'])->all())->first();
        $server = Server::where('unique_id', $id)->firstOrFail();

        if ($server->user->id != $user->id) {
            return abort(403, 'Server belongs to different user');
        }

        // TODO: Reboot server

        return response('', 200);
    }

    /**
     * @SWG\Patch(
     *      path="/servers/{id}/rename",
     *      summary="Rename Server",
     *      tags={"servers"},
     *      description="The `/servers/{id}/rename` route is used to rename a server that was previously ordered via nanobox. This route SHOULD NOT hold open the request until the server is completely renamed. It should return immediately once the order to rename has been submitted.",
     *      @SWG\Parameter(
     *          name="payload",
     *          description="User creds and server ID",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"auth"},
     *              @SWG\Property(
     *                  property="auth",
     *                  type="object",
     *                  description="key/value pairs containing the `credential_fields` and their corresponding values as populated by the user. This will provide the necessary values to authorize the user within this provider",
     *                  required={"host","user","realm","password"},
     *                  @SWG\Property(
     *                      property="host",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="user",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="realm",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="password",
     *                      type="string",
     *                  ),
     *              ),
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="the server id (added here as a convenience)",
     *              ),
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
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @SWG\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @SWG\Schema(
     *              type="object",
     *              required={"errors"},
     *              @SWG\Property(
     *                  property="errors",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string",
     *                  ),
     *              ),
     *          ),
     *      ),
     * )
     */
    public function rename(Request $request, $id)
    {
        $user   = User::where(collect($request->json('auth'))->only(['host', 'user', 'realm'])->all())->first();
        $server = Server::where('unique_id', $id)->firstOrFail();

        if ($server->user->id != $user->id) {
            return abort(403, 'Server belongs to different user');
        }

        $server->name = $request->json('name');

        // TODO: Rename server

        $server->save();

        return response('', 200);
    }
}
