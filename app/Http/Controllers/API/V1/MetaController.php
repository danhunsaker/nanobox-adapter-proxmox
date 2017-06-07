<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Region;
use App\ServerSize;
use App\User;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Proxmox;
use ProxmoxVE\Exception\AuthenticationException;
use ProxmoxVE\Exception\MalformedCredentialsException;

class MetaController extends Controller
{
    /**
     * @SWG\Get(
     *      path="/meta",
     *      summary="Gathering Metadata",
     *      tags={"meta"},
     *      operationId="meta",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#meta",
     *      },
     *      description="The `/meta` route is used to provide Nanobox with various pieces of metadata that will be used for displaying information in the dashboard and for requesting authentication information from the users.",
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          examples={
     *              "application/json"={
     *                  "id"="proxmox",
     *                  "name"="Proxmox",
     *                  "server_nick_name"="VM",
     *                  "default_region"="own",
     *                  "default_size"="512mb",
     *                  "default_plan"="1c",
     *                  "can_reboot"=true,
     *                  "can_rename"=true,
     *                  "internal_iface"="eth1",
     *                  "external_iface"="eth0",
     *                  "ssh_user"="root",
     *                  "ssh_auth_method"="password",
     *                  "ssh_key_method"="reference",
     *                  "bootstrap_script"="https://s3.amazonaws.com/tools.nanobox.io/bootstrap/ubuntu.sh",
     *                  "credential_fields"={
     *                      {
     *                          "key"="hostname",
     *                          "label"="Proxmox server hostname"
     *                      },
     *                      {
     *                          "key"="port",
     *                          "label"="Proxmox server port"
     *                      },
     *                      {
     *                          "key"="username",
     *                          "label"="Proxmox server username"
     *                      },
     *                      {
     *                          "key"="realm",
     *                          "label"="Proxmox server realm"
     *                      },
     *                      {
     *                          "key"="password",
     *                          "label"="Proxmox server password"
     *                      },
     *                      {
     *                          "key"="node",
     *                          "label"="Proxmox node name"
     *                      },
     *                      {
     *                          "key"="storage",
     *                          "label"="Proxmox storage name"
     *                      }
     *                  },
     *                  "instructions"="Enter the hostname of your Proxmox server, the port (default: 8006), your username, realm, and password for logging into your server, the name of the node you wish to deploy to (default: ""pve""), and the name of the storage to create Nanobox VMs on (default: ""local-lvm""). Leave a field blank to use the default. You need to have a Proxmox server with a public IP address to use this provider.",
     *              },
     *          },
     *          @SWG\Schema(
     *              type="object",
     *              required={"id","name","server_nick_name","default_region","default_size","can_reboot","can_rename","internal_iface","external_iface","ssh_user","ssh_auth_method","ssh_key_method","bootstrap_script","credential_fields"},
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
     *                  property="server_nick_name",
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
     *                  property="default_plan",
     *                  type="string",
     *                  description="the id of the default plan in which the default size is ordered",
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
     *                  property="internal_iface",
     *                  type="string",
     *                  description="Internal interface. e.g. eth1",
     *              ),
     *              @SWG\Property(
     *                  property="external_iface",
     *                  type="string",
     *                  description="External interface. e.g. eth0",
     *              ),
     *              @SWG\Property(
     *                  property="ssh_user",
     *                  type="string",
     *                  description="The ssh user Nanobox can use for ssh access to bootstrap the server. e.g. root",
     *              ),
     *              @SWG\Property(
     *                  property="ssh_auth_method",
     *                  type="string",
     *                  enum={"key","password"},
     *                  description="will either be key or password",
     *              ),
     *              @SWG\Property(
     *                  property="ssh_key_method",
     *                  type="string",
     *                  enum={"reference","object"},
     *                  description="will either be reference or object. Vhen set to 'reference', Nanobox will first create the SSH key in the user's provider account, then pass a reference to it when servers are created. When set to 'object', Nanobox will pass the actual public SSH key that should be installed on the server.",
     *              ),
     *              @SWG\Property(
     *                  property="bootstrap_script",
     *                  type="string",
     *                  description="The script that should be used to bootstrap the server. e.g. https://s3.amazonaws.com/tools.nanobox.io/bootstrap/ubuntu.sh",
     *              ),
     *              @SWG\Property(
     *                  property="credential_fields",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      required={"key","label"},
     *                      @SWG\Property(
     *                          property="key",
     *                          type="string",
     *                          description="suffix for Auth- headers used to identify each auth field",
     *                      ),
     *                      @SWG\Property(
     *                          property="label",
     *                          type="string",
     *                          description="text to display to the user for each auth field",
     *                      ),
     *                  ),
     *                  description="array of hashes that includes field keys and labels necessary to authenticate with the provider",
     *              ),
     *              @SWG\Property(
     *                  property="instructions",
     *                  type="string",
     *                  description="string that contains instructions for how to setup authentication with the provider",
     *              ),
     *          )
     *      )
     * )
     */
    public function meta()
    {
        return response()->json([
            'id'                => 'proxmox',
            'name'              => 'Proxmox',
            'server_nick_name'  => 'VM',
            'default_region'    => Region::first()->code,
            'default_size'      => ServerSize::first()->code,
            'default_plan'      => ServerSize::first()->serverPlans->first()->code,
            'can_reboot'        => true,
            'can_rename'        => true,
            'internal_iface'    => 'eth1',
            'external_iface'    => 'eth0',
            'ssh_user'          => 'root',
            'ssh_auth_method'   => 'password',
            'ssh_key_method'    => 'reference',
            'bootstrap_script'  => 'https://s3.amazonaws.com/tools.nanobox.io/bootstrap/ubuntu.sh',
            'credential_fields' => [
                ['key' => 'hostname', 'label' => 'Proxmox server hostname'],
                ['key' => 'port',     'label' => 'Proxmox server port'],
                ['key' => 'username', 'label' => 'Proxmox server username'],
                ['key' => 'realm',    'label' => 'Proxmox server realm'],
                ['key' => 'password', 'label' => 'Proxmox server password'],
                ['key' => 'node',     'label' => 'Proxmox node name'],
                ['key' => 'storage',  'label' => 'Proxmox storage name'],
            ],
            'instructions' => 'Enter the hostname of your Proxmox server, the port (default: 8006), ' .
                'your username, realm, and password for logging into your server, the name of the node ' .
                'you wish to deploy to (default: "pve"), and the name of the storage to create Nanobox ' .
                'VMs on (default: "local-lvm"). Leave a field blank to use the default. You need to ' .
                'have a Proxmox server with a public IP address to use this provider.',
        ]);
    }

    /**
     * @SWG\Get(
     *      path="/catalog",
     *      summary="Requesting the Catalog",
     *      tags={"meta"},
     *      operationId="catalog",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#catalog",
     *      },
     *      description="The `/catalog` route is used to provide Nanobox with a catalog of server sizes and options, within the available geographic regions.",
     *      @SWG\Response(
     *          response=200,
     *          description="The response data should be a list (array) of regions. Each region should contain a list of plans. It is not necessary to have multiple regions, however the structure will be the same regardless. Additionally, your integration may only have one classification of server types, or you may have high-cpu, high-ram, or high-IO options. A plan is a grouping of server sizes within a classification.",
     *          examples={
     *              "application/json"={
     *                  {
     *                      "id": "own",
     *                      "name": "Self-Owned/Operated",
     *                      "plans": {
     *                          {
     *                              "id": "1c",
     *                              "name": "Single Core",
     *                              "specs": {
     *                                  {
     *                                      "id": "512mb",
     *                                      "ram": 512,
     *                                      "cpu": 1,
     *                                      "disk": 40,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                                  {
     *                                      "id": "1gb",
     *                                      "ram": 1024,
     *                                      "cpu": 1,
     *                                      "disk": 40,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                              },
     *                          },
     *                          {
     *                              "id": "2c",
     *                              "name": "Dual Core",
     *                              "specs": {
     *                                  {
     *                                      "id": "512mb2c",
     *                                      "ram": 512,
     *                                      "cpu": 2,
     *                                      "disk": 40,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                                  {
     *                                      "id": "1gb2c",
     *                                      "ram": 1024,
     *                                      "cpu": 2,
     *                                      "disk": 40,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                                  {
     *                                      "id": "2gb2c",
     *                                      "ram": 2048,
     *                                      "cpu": 2,
     *                                      "disk": 40,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                              },
     *                          },
     *                          {
     *                              "id": "4c",
     *                              "name": "Quad Core",
     *                              "specs": {
     *                                  {
     *                                      "id": "1gb4c",
     *                                      "ram": 1024,
     *                                      "cpu": 4,
     *                                      "disk": 40,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                                  {
     *                                      "id": "2gb4c",
     *                                      "ram": 2048,
     *                                      "cpu": 4,
     *                                      "disk": 40,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                                  {
     *                                      "id": "4gb4c",
     *                                      "ram": 4096,
     *                                      "cpu": 4,
     *                                      "disk": 40,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                              },
     *                          },
     *                          {
     *                              "id": "4c+",
     *                              "name": "Quad Core High Capacity",
     *                              "specs": {
     *                                  {
     *                                      "id": "1gb4c+",
     *                                      "ram": 1024,
     *                                      "cpu": 4,
     *                                      "disk": 250,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                                  {
     *                                      "id": "2gb4c+",
     *                                      "ram": 2048,
     *                                      "cpu": 4,
     *                                      "disk": 250,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                                  {
     *                                      "id": "4gb4c+",
     *                                      "ram": 4096,
     *                                      "cpu": 4,
     *                                      "disk": 250,
     *                                      "transfer": null,
     *                                      "dollars_per_hr": 0,
     *                                      "dollars_per_mo": 0
     *                                  },
     *                              },
     *                          },
     *                      },
     *                  },
     *              },
     *          },
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
     *                          required={"id","name","specs"},
     *                          @SWG\Property(
     *                              property="id",
     *                              type="string",
     *                              description="unique plan identifier",
     *                          ),
     *                          @SWG\Property(
     *                              property="name",
     *                              type="string",
     *                              description="the classification of the server options within this plan. The name should indicate to the user what kinds of workloads these server options are ideal for. For instance: ""Standard"" or ""High CPU""",
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
                        'id'    => $item->code,
                        'name'  => $item->name,
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
     *      operationId="verify",
     *      externalDocs={
     *          "description"="Official documentation here",
     *          "url"="https://docs.nanobox.io/providers/create/#verify",
     *      },
     *      description="The `/verify` route is used to verify a user's account credentials. The `credential_fields` specified in the metadata will be provided in the dashboard and required to be filled before the user can use this provider. After the credentials are provided, Nanobox will call this route to verify that the account credentials provided by the user are valid.",
     *      @SWG\Parameter(ref="#/parameters/Auth-Hostname"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Port"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Username"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Realm"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Password"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Node"),
     *      @SWG\Parameter(ref="#/parameters/Auth-Storage"),
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
    public function verify(Request $request)
    {
        $user           = User::firstOrNew(collect($request->creds)->only(['hostname', 'port', 'username', 'realm'])->all());
        $user->password = $request->creds['password'];

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

        if ( ! collect(Proxmox::get('/nodes')['data'])->contains('node', $request->creds['node'])) {
            abort(403, "Provided node \"{$request->creds['node']}\" does not exist");
        }
        if ( ! collect(Proxmox::get('/storage')['data'])->contains('storage', $request->creds['storage'])) {
            abort(403, "Provided storage \"{$request->creds['storage']}\" does not exist");
        }

        $user->save();

        return response('', 200);
    }
}
