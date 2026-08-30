<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = 'login|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $credential = $request->input('username');
        $password   = $request->input('password');

        // Try username or email
        $user = AuthUser::withoutGlobalScope('active')
            ->where(function ($q) use ($credential) {
                $q->where('username', $credential)->orWhere('email', $credential);
            })
            ->first();

        if (!$user) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['username' => 'Username atau email tidak ditemukan.']);
        }

        if ($user->is_deleted) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['username' => 'Akun tidak tersedia.']);
        }

        if (!$user->active) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['username' => 'Akun Anda tidak aktif.']);
        }

        if (!\Hash::check($password, $user->password)) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['password' => 'Password salah.']);
        }

        RateLimiter::clear($key);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Update last_login
        $user->timestamps = false;
        $user->last_login = now();
        $user->save();
        $user->timestamps = true;

        return $this->redirectByRole($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda berhasil keluar.');
    }

    private function redirectByRole(AuthUser $user)
    {
        $roleName = strtolower($user->role?->name ?? '');
        if (str_contains($roleName, 'admin')) {
            return redirect()->route('admin.dashboard');
        }
        if (str_contains($roleName, 'driver')) {
            return redirect()->route('driver.dashboard');
        }
        return redirect()->route('home');
    }
}
