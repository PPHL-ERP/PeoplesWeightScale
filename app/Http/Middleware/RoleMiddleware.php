<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    // public function handle(Request $request, Closure $next,  $roles): Response
    // {
    //     $user = $request->user();
    //     if (!$user) abort(403);
    //     $required = explode('|', $roles); // e.g. role:Admin|Manager

    //     if ($user->hasRole($required)) return $next($request);
    //     abort(403, 'You do not have the required role.');
    //     }

        public function handle(Request $request, Closure $next, $roles)
    {
        $user = $request->user();

        // ✅ auth()->user() না থাকলে session('user.id') থেকে ইউজার লোড করো
        if (!$user && session()->has('user')) {
            $userId = data_get(session('user'), 'id'); // অথবা 'user.id'
            if ($userId) $user = User::find($userId);
        }

        if (!$user) abort(403);

        if ($user->isSuperAdmin || $user->hasRole($roles)) {
            return $next($request);
        }

        abort(403, 'You do not have role.');
    }
}
