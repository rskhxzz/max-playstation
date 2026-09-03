<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = AuthUser::withoutGlobalScope('active')
            ->where('is_deleted', false)
            ->with('role')
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = AuthRole::all();
        return view('admin.users.form', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:auth_user,username',
            'email'    => 'required|email|max:100|unique:auth_user,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id'  => 'required|string|exists:auth_role,id',
            'active'   => 'boolean',
        ]);

        $data['id']         = (string) Str::uuid();
        $data['password']   = \Hash::make($data['password']);
        $data['active']     = $request->boolean('active', true);
        $data['created_by'] = auth('admin')->id();
        $data['is_deleted'] = false;

        AuthUser::withoutGlobalScope('active')->create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $user  = AuthUser::withoutGlobalScope('active')->findOrFail($id);
        $roles = AuthRole::all();
        return view('admin.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $user = AuthUser::withoutGlobalScope('active')->findOrFail($id);

        $rules = [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:auth_user,username,' . $id,
            'email'    => 'required|email|max:100|unique:auth_user,email,' . $id,
            'role_id'  => 'required|string|exists:auth_role,id',
            'active'   => 'boolean',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $data = $request->validate($rules);

        if ($request->filled('password')) {
            $data['password'] = \Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $data['active']     = $request->boolean('active', true);
        $data['updated_by'] = auth('admin')->id();

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $user = AuthUser::withoutGlobalScope('active')->findOrFail($id);

        if ($user->id === auth('admin')->id()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus akun sendiri.']);
        }

        $user->is_deleted = true;
        $user->active     = false;
        $user->updated_by = auth('admin')->id();
        $user->save();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
