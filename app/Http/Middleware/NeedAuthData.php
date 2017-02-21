<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;

class NeedAuthData
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     *
     *  @SWG\Parameter(
     *      name="Auth-Hostname",
     *      description="Proxmox server hostname",
     *      in="header",
     *      type="string",
     *      required=true,
     *  ),
     *  @SWG\Parameter(
     *      name="Auth-Port",
     *      description="Proxmox server port",
     *      in="header",
     *      type="integer",
     *      required=false,
     *      default=8006,
     *  ),
     *  @SWG\Parameter(
     *      name="Auth-Username",
     *      description="Proxmox server username",
     *      in="header",
     *      type="string",
     *      required=true,
     *  ),
     *  @SWG\Parameter(
     *      name="Auth-Realm",
     *      description="Proxmox server realm",
     *      in="header",
     *      type="string",
     *      required=true,
     *  ),
     *  @SWG\Parameter(
     *      name="Auth-Password",
     *      description="Proxmox server password",
     *      in="header",
     *      type="string",
     *      required=true,
     *  ),
     *  @SWG\Parameter(
     *      name="Auth-Node",
     *      description="Proxmox node name",
     *      in="header",
     *      type="string",
     *      required=false,
     *      default="pve",
     *  ),
     *  @SWG\Parameter(
     *      name="Auth-Storage",
     *      description="Proxmox storage name",
     *      in="header",
     *      type="string",
     *      required=false,
     *      default="local-lvm",
     *  ),
     */
    public function handle(Request $request, Closure $next, $mode = 'valid')
    {
        $creds = [
            'hostname' => $request->header('auth-hostname'),
            'port'     => $request->header('auth-port', 8006),
            'username' => $request->header('auth-username'),
            'realm'    => $request->header('auth-realm'),
            'password' => $request->header('auth-password'),
            'node'     => $request->header('auth-node', 'pve'),
            'storage'  => $request->header('auth-storage', 'local-lvm'),
        ];

        if (empty($creds['hostname']) || empty($creds['username']) || empty($creds['realm']) || empty($creds['password'])) {
            return abort(401, 'Missing one or more required creds');
        }

        if ($mode === 'valid') {
            $request->user = User::where(collect($creds)->only(['hostname', 'port', 'username', 'realm'])->all())->firstOrFail();
        }

        $request->creds = $creds;

        return $next($request);
    }
}
