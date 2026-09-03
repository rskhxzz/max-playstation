<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan request dilakukan oleh user yang sudah login via guard 'driver'
 * dan memiliki role Driver. Admin tidak bisa menembus middleware ini.
 */
class AuthDriver
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek guard driver
        if (!Auth::guard('driver')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::guard('driver')->user();

        // Double-check role (harus Driver)
        if (!$user->hasRole('driver')) {
            Auth::guard('driver')->logout();
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Akses ditolak.'], 403);
            }
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
