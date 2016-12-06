<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Region;
use App\ServerSize;
use App\User;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    /**
     * @SWG\Get(
     *      path="/meta",
     *      summary="Gathering Metadata",
     *      tags={"meta"},
     *      description="The `/meta` route is used to provide Nanobox with various pieces of metadata that will be used for displaying information in the dashboard and for requesting authentication information from the users.",
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              required={"id","name","server_nickname","default_region","default_size","can_reboot","can_rename","credential_fields"},
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *                  description="some unique identifier",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="display name used in the dashboard",
     *              ),
     *              @SWG\Property(
     *                  property="server_nickname",
     *                  type="string",
     *                  description="what this provider calls their servers",
     *              ),
     *              @SWG\Property(
     *                  property="default_region",
     *                  type="string",
     *                  description="the default region to launch servers when not specified",
     *              ),
     *              @SWG\Property(
     *                  property="default_size",
     *                  type="string",
     *                  description="default server size to use when creating an app",
     *              ),
     *              @SWG\Property(
     *                  property="can_reboot",
     *                  type="boolean",
     *                  description="boolean to determine if we can reboot the server through the api",
     *              ),
     *              @SWG\Property(
     *                  property="can_rename",
     *                  type="boolean",
     *                  description="boolean to determine if we can rename the server through the api",
     *              ),
     *              @SWG\Property(
     *                  property="ssh_auth_method",
     *                  type="string",
     *                  enum={"key","password"},
     *                  description="will either be key or password",
     *              ),
     *              @SWG\Property(
     *                  property="credential_fields",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string",
     *                  ),
     *                  description="array of field names that will be submitted with each server action used to validate the user account",
     *              )
     *          )
     *      )
     * )
     */
    public function meta()
    {
        return response()->json([
            'id'                => 'proxmox',
            'name'              => 'Proxmox',
            'server_nickname'   => 'VM',
            'default_region'    => Region::first()->code,
            'default_size'      => ServerSize::first()->code,
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
     * @SWG\Get(
     *      path="/catalog",
     *      summary="Requesting the Catalog",
     *      tags={"meta"},
     *      description="The `/catalog` route is used to provide nanobox with a catalog of server sizes and options, within the available geographic regions.",
     *      @SWG\Response(
     *          response=200,
     *          description="The response data should be a list (array) of regions. Each region should contain a list of plans. It is not necessary to have multiple regions, however the structure will be the same regardless. Additionally, your integration may only have one classification of server types, or you may have high-cpu, high-ram, or high-IO options. A plan is a grouping of server sizes within a classification.",
     *          @SWG\Schema(
     *              type="array",
     *              description="Each region in the catalog consists of the following",
     *              @SWG\Items(
     *                  type="object",
     *                  required={"id","name","plans"},
     *                  @SWG\Property(
     *                      property="id",
     *                      type="string",
     *                      description="unique region identifier to be used when ordering a server",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      description="the visual identifier for the customer",
     *                  ),
     *                  @SWG\Property(
     *                      property="plans",
     *                      type="array",
     *                      description="A grouping of server sizes within a classification. Each plan consists of the following",
     *                      @SWG\Items(
     *                          type="object",
     *                          required={"title","specs"},
     *                          @SWG\Property(
     *                              property="title",
     *                              type="string",
     *                              description="the classification of the server options within this plan. The title should indicate to the user what kinds of workloads these server options are ideal for. For instance: ""Standard"" or ""High CPU""",
     *                          ),
     *                          @SWG\Property(
     *                              property="specs",
     *                              type="array",
     *                              description="the list of server options within this plan. Each spec should have the following fields",
     *                              @SWG\Items(
     *                                  type="object",
     *                                  required={"id","ram","cpu","disk","transfer","dollars_per_hr","dollars_per_mo"},
     *                                  @SWG\Property(
     *                                      property="id",
     *                                      type="string",
     *                                      description="a unique identifier that will be used when ordering a server",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="ram",
     *                                      type="integer",
     *                                      description="a visual indication to the user informing the amount of RAM is provided",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="cpu",
     *                                      type="integer",
     *                                      description="a visual indication to the user informing the amount of CPUs or CPU cores",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="disk",
     *                                      type="integer",
     *                                      description="a visual indication to the user informing the amount or size of disk",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="transfer",
     *                                      type="integer",
     *                                      description="a visual indication to the user informing the amount of data transfer allowed per month for this server",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="dollars_per_hr",
     *                                      type="number",
     *                                      format="currency",
     *                                      description="a visual indication to the user informing the cost of running this server per hour",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="dollars_per_mo",
     *                                      type="number",
     *                                      format="currency",
     *                                      description="a visual indication to the user informing the cost of running this server per month",
     *                                  ),
     *                              ),
     *                          ),
     *                      ),
     *                  ),
     *              ),
     *          )
     *      )
     * )
     */
    public function catalog()
    {
        return response()->json(Region::all()->map(function ($item, $key) {
            return [
                'id'    => $item->code,
                'name'  => $item->name,
                'plans' => $item->serverPlans->map(function ($item, $key) {
                    return [
                        'title' => $item->title,
                        'specs' => $item->serverSizes->map(function ($item, $key) {
                            return [
                                'id'             => $item->code,
                                'ram'            => $item->ram,
                                'cpu'            => $item->cpu,
                                'disk'           => $item->disk,
                                'transfer'       => $item->transfer,
                                'dollars_per_hr' => $item->dollars_per_hr,
                                'dollars_per_mo' => $item->dollars_per_mo,
                            ];
                        }),
                    ];
                }),
            ];
        }));
    }

    /**
     *  @SWG\Post(
     *      path="/verify",
     *      summary="Verify the account credentials",
     *      tags={"meta"},
     *      description="The `/verify` route is used to verify a user's account credentials. The `credential_fields` specified in the metadata will be provided in the dashboard and required to be filled before the user can use this provider. After the credentials are provided, nanobox will call this route to verify that the account credentials provided by the user are valid.",
     *      @SWG\Parameter(
     *          name="payload",
     *          description="key/value pairs containing the `credential_fields` and their corresponding values as populated by the user. This will provide the necessary values to authorize the user within this provider",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *                 required={"auth"},
     *              @SWG\Property(
     *                  property="auth",
     *                  type="object",
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
     *          ),
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="string",
     *              default=""
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
    public function verify(Request $request)
    {
        $user = User::firstOrCreate(collect($request->json('auth'))->only(['host', 'user', 'realm'])->all());

        // TODO: Verify the credentials are currently working

        return response('', 200);
    }

    /**
     *  @SWG\Post(
     *      path="/keys",
     *      summary="Adding SSH Keys",
     *      tags={"meta"},
     *      description="<p>The `/keys` route is used to authorize nanobox with the user's account that will be ordering servers. After ordering a server, nanobox will need to SSH into the server to provision it. Nanobox will pre-generate an SSH key for the user's account and the authorization route allows nanobox to register this key with the user's account on this provider so that nanobox can access the server after it is ordered.</p> <p>NOTE: This route is not required if your provider uses passwords for SSH instead of SSH keys, assuming the password to access the server is returned in the order server payload.</p>",
     *      @SWG\Parameter(
     *          name="payload",
     *          description="User creds, key ID, and key contents to save",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              required={"auth","name","key"},
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
     *                  description="the name of this key that will be used as an identifier when ordering a server",
     *              ),
     *              @SWG\Property(
     *                  property="key",
     *                  type="string",
     *                  description="The public key to register with the user's account. It is assumed that this public key will be installed on every server launched by this integration.",
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
     *                  description="fingerprint or key identifier to use when ordering servers",
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
    public function keys(Request $request)
    {
        $creds = $request->json('auth');
        $name  = $request->json('name');
        $key   = $request->json('key');

        Key::create(compact('name', 'key'));

        return response()->json(['id' => $name], 201);
    }
}
