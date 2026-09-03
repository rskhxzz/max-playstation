@extends('layouts.admin')
@section('title', isset($user) ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page-title', isset($user) ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary mb-3" style="border-radius:8px;">
    <i class="bi bi-arrow-left me-1"></i>Kembali
</a>
<div class="card" style="max-width:600px;">
    <div class="card-header"><h6>{{ isset($user) ? 'Edit' : 'Tambah' }} Pengguna</h6></div>
    <div class="card-body">
        <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST">
            @csrf @if(isset($user)) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name ?? '') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                           value="{{ old('username', $user->username ?? '') }}">
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email ?? '') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '*' }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           {{ isset($user) ? '' : 'required' }}>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id ?? '') === $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" value="1"
                               {{ old('active', $user->active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
