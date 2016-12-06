<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    /**
     * @SWG\Get(
     *      path="/meta",
     *      summary="Get a listing of Nanobox dashboard metadata.",
     *      tags={"meta"},
     *      description="Get dashboard metadata",
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              required={"id","name","server_nickname","default_region","default_size","can_reboot","can_rename","credential_fields"},
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="server_nickname",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="default_region",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="default_size",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="can_reboot",
     *                  type="boolean",
     *              ),
     *              @SWG\Property(
     *                  property="can_rename",
     *                  type="boolean",
     *              ),
     *              @SWG\Property(
     *                  property="ssh_auth_method",
     *                  type="string",
     *                  enum={"key","password"},
     *              ),
     *              @SWG\Property(
     *                  property="credential_fields",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string",
     *                  ),
     *              )
     *          )
     *      )
     * )
     */
    public function meta(Request $request)
    {
        return response()->json([
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
     * @SWG\Get(
     *      path="/catalog",
     *      summary="Provide Nanobox with a catalog of server sizes and options, within the available geographic regions.",
     *      tags={"meta"},
     *      description="Get server catalog",
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  required={"id","name","plans"},
     *                  @SWG\Property(
     *                      property="id",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="plans",
     *                      type="array",
     *                      @SWG\Items(
     *                          type="object",
     *                          required={"title","specs"},
     *                          @SWG\Property(
     *                              property="title",
     *                              type="string",
     *                          ),
     *                          @SWG\Property(
     *                              property="specs",
     *                              type="array",
     *                              @SWG\Items(
     *                                  type="object",
     *                                  required={"id","ram","cpu","disk","transfer","dollars_per_hr","dollars_per_mo"},
     *                                  @SWG\Property(
     *                                      property="id",
     *                                      type="string",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="ram",
     *                                      type="integer",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="cpu",
     *                                      type="integer",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="disk",
     *                                      type="integer",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="transfer",
     *                                      type="integer",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="dollars_per_hr",
     *                                      type="number",
     *                                      format="currency",
     *                                  ),
     *                                  @SWG\Property(
     *                                      property="dollars_per_mo",
     *                                      type="number",
     *                                      format="currency",
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
    public function catalog(Request $request)
    {
        return response()->json([
            [
                'id'    => 'own',
                'name'  => 'Self-Owned/Operated',
                'plans' => [
                    [
                        'title' => 'Single Core',
                        'specs' => [
                            ['id' => '512mb', 'ram' => 512, 'cpu' => 1, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                            ['id' => '1gb', 'ram' => 1024, 'cpu' => 1, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                        ],
                    ],
                    [
                        'title' => 'Dual Core',
                        'specs' => [
                            ['id' => '512mb2c', 'ram' => 512, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                            ['id' => '1gb2c', 'ram' => 1024, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                            ['id' => '2gb2c', 'ram' => 2048, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                        ],
                    ],
                    [
                        'title' => 'Quad Core',
                        'specs' => [
                            ['id' => '1gb4c', 'ram' => 1024, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                            ['id' => '2gb4c', 'ram' => 2048, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                            ['id' => '4gb4c', 'ram' => 4096, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                        ],
                    ],
                    [
                        'title' => 'Quad Core High Capacity',
                        'specs' => [
                            ['id' => '1gb4c+', 'ram' => 1024, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                            ['id' => '2gb4c+', 'ram' => 2048, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                            ['id' => '4gb4c+', 'ram' => 4096, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     *  @SWG\Post(
     *      path="/verify",
     *      summary="Verify a user's account credentials.",
     *      tags={"meta"},
     *      description="Verify credentials",
     *      @SWG\Parameter(
     *          name="payload",
     *          description="User creds to test",
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
        $creds = $request->json('auth');

        if (empty($creds['host']) || empty($creds['user']) || empty($creds['realm']) || empty($creds['password'])) {
            return response()->json(['errors' => ['Missing one or more creds']], 401);
        }

        return response('', 200);
    }

    /**
     *  @SWG\Post(
     *      path="/keys",
     *      summary="Allow Nanobox to register an SSH key with the user's account on this provider.",
     *      tags={"meta"},
     *      description="Register SSH key",
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
     *              ),
     *              @SWG\Property(
     *                  property="key",
     *                  type="string",
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

        if (empty($creds['host']) || empty($creds['user']) || empty($creds['realm']) || empty($creds['password'])) {
            return response()->json(['errors' => ['Missing one or more creds']], 401);
        }

        return response()->json(['id' => $name], 201);
    }
}
