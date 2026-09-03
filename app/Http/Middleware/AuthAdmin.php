<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan request dilakukan oleh user yang sudah login via guard 'admin'
 * dan memiliki role Admin. Driver tidak bisa menembus middleware ini.
 */
class AuthAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek guard admin
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::guard('admin')->user();

        // Double-check role (harus Admin)
        if (!$user->hasRole('admin')) {
            Auth::guard('admin')->logout();
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Akses ditolak.'], 403);
            }
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
