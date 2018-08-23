<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KeyController extends Controller
{
    /**
     *  @SWG\Post(
     *      path="/keys",
     *      summary="Create SSH Key",
     *      tags={"keys"},
     *      operationId="create-ssh-key",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#create-ssh-key",
     *      },
     *      description="<p>The `/keys` route is used to authorize Nanobox with the user's account that will be ordering servers. After ordering a server, Nanobox needs to SSH into the server to provision it. Nanobox will pre-generate an SSH key for the user's account and the authorization route allows Nanobox to register this key with the user's account on this provider so that Nanobox can access the server after it is ordered.</p> <p>NOTE: This route is *not* required if your provider uses passwords for SSH instead of SSH keys, assuming the password to access the server is returned in the order server payload.</p>",
     *      @SWG\Parameter(ref="#/parameters/Auth-Hostname"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Port"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Username"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Realm"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Password"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Node"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Storage"),
     *      @SWG\Parameter(
     *          name="payload",
     *          description="Key ID and contents to save",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"id","key"},
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="the user-friendly name of the key",
     *              ),
     *              @SWG\Property(
     *                  property="key",
     *                  type="string",
     *                  description="The public key to register with the user's account. It is assumed that this public key will be installed on every server launched by this integration.",
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
     *                  "id"="provider-key-ID",
     *              },
     *          },
     *          @SWG\Schema(
     *              type="object",
     *              required={"id"},
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="fingerprint or key identifier to use when ordering servers",
     *              ),
     *          ),
     *      ),
     *  )
     */
    public function store(Request $request)
    {
        $user = $request->user;
        $name = $request->json('id');
        $key  = $request->json('key');
        $code = Str::slug("{$user->hostname} {$user->port} {$user->username} {$user->realm} {$name}");

        $key = Key::firstOrNew(compact('code', 'name', 'key'));
        $key->user()->associate($user);
        $key->save();

        return response()->json(['id' => $code], 201);
    }

    /**
     *  @SWG\Get(
     *      path="/keys/{id}",
     *      summary="Query SSH Key",
     *      tags={"keys"},
     *      operationId="query-ssh-key",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#query-ssh-key",
     *      },
     *      description="<p>The `GET /keys/{id}` route is used by Nanobox to query the existence of previously created key.</p> <p>NOTE: This route is *not* required if your provider uses passwords for SSH instead of SSH keys, assuming the password to access the server is returned in the order server payload.</p>",
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
     *          description="the key id",
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
     *                  "id"="provider-key-ID",
     *                  "name"="nanobox-provider-account-ID",
     *                  "public_key"="CONTENTS OF PUBLIC KEY",
     *              },
     *          },
     *          @SWG\Schema(
     *              type="object",
     *              required={"id","name","public_key"},
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="fingerprint or key identifier to use when ordering servers",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="the user-friendly name of the key",
     *              ),
     *              @SWG\Property(
     *                  property="public_key",
     *                  type="string",
     *                  description="contents of public key",
     *              ),
     *          ),
     *      ),
     *  )
     */
    public function show(Request $request, $id)
    {
        $user = $request->user;
        $key  = Key::where('code', $id)->firstOrFail();

        if ($key->user->id != $user->id) {
            return abort(403, 'Key belongs to different user');
        }

        return response()->json([
            'id'         => $key->code,
            'name'       => $key->name,
            'public_key' => $key->key,
        ], 201);
    }

    /**
     *  @SWG\Delete(
     *      path="/keys/{id}",
     *      summary="Delete SSH Key",
     *      tags={"keys"},
     *      operationId="delete-ssh-key",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#delete-ssh-key",
     *      },
     *      description="<p>The `DELETE /keys/{id}` route is used to cancel a key that was previously created via Nanobox.</p> <p>NOTE: This route is *not* required if your provider uses passwords for SSH instead of SSH keys, assuming the password to access the server is returned in the order server payload.</p>",
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
     *          description="the key id",
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
     *  )
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user;
        $key  = Key::where('code', $id)->firstOrFail();

        if ($key->user->id != $user->id) {
            return abort(403, 'Key belongs to different user');
        }

        $key->delete();

        return response('', 200);
    }
}
