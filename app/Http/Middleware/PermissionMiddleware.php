<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    // public function handle(Request $request, Closure $next, $permission): Response
    // {
    //     $user = $request->user();
    //     if (!$user) abort(403);

    //     if ($user->hasPermission($permission)) return $next($request);
    //     abort(403, 'You do not have permission to perform this action.');
    // }

public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();

        // ✅ auth()->user() না থাকলে session('user.id') থেকে ইউজার লোড করো
        if (!$user && session()->has('user')) {
            $userId = data_get(session('user'), 'id'); // অথবা 'user.id'
            if ($userId) $user = User::find($userId);
        }

        if (!$user) abort(403);

        if ($user->isSuperAdmin || $user->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, 'You do not have permission.');
    }

}
