<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AuthUser;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Halaman login selalu ditampilkan agar Admin dan Driver
        // bisa login bersamaan dari browser yang sama.
        // Jika sudah login sebagai KEDUANYA, arahkan ke Admin (prioritas).
        if (Auth::guard('admin')->check()) {
            // Sudah login sebagai Admin, tapi mungkin mau login Driver juga —
            // tetap tampilkan form agar tidak memblokir login Driver.
            // Redirect hanya jika tidak ada role baru yang mau di-login.
            // Solusi: tetap tampilkan form, biarkan user memilih.
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

        // Cari user tanpa global scope (perlu cek active secara manual)
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

        if (!Hash::check($password, $user->password)) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['password' => 'Password salah.']);
        }

        RateLimiter::clear($key);

        $roleName = strtolower($user->role?->name ?? '');

        // Login ke guard yang sesuai role — sehingga session Admin dan Driver TERPISAH
        if (str_contains($roleName, 'admin')) {
            Auth::guard('admin')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $this->updateLastLogin($user);
            return redirect()->route('admin.dashboard');
        }

        if (str_contains($roleName, 'driver')) {
            Auth::guard('driver')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $this->updateLastLogin($user);
            return redirect()->route('driver.dashboard');
        }

        // Role tidak dikenal
        throw ValidationException::withMessages(['username' => 'Role akun tidak dikenali.']);
    }

    public function logout(Request $request)
    {
        // Logout hanya guard yang sedang aktif sesuai referer/parameter
        // Cek dari mana request logout datang
        $from = $request->input('from', '');

        if ($from === 'driver' || str_contains($request->header('Referer', ''), '/driver/')) {
            // Logout Driver saja
            Auth::guard('driver')->logout();
        } elseif ($from === 'admin' || str_contains($request->header('Referer', ''), '/admin/')) {
            // Logout Admin saja
            Auth::guard('admin')->logout();
        } else {
            // Fallback: logout keduanya
            Auth::guard('admin')->logout();
            Auth::guard('driver')->logout();
        }

        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar.');
    }
    
    private function updateLastLogin(AuthUser $user): void
    {
        $user->timestamps = false;
        $user->last_login = now();
        $user->save();
        $user->timestamps = true;
    }
}
