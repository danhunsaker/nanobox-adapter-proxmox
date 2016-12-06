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
     */
    public function handle(Request $request, Closure $next, $mode = 'valid')
    {
        $creds = $request->json('auth');

        if (empty($creds['host']) || empty($creds['user']) || empty($creds['realm']) || empty($creds['password'])) {
            return abort(401, 'Missing one or more creds');
        }

        if ($mode === 'valid') {
            User::where(collect($creds)->only(['host', 'user', 'realm'])->all())->firstOrFail();
        }

        return $next($request);
    }
}
