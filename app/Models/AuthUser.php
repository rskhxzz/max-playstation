<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;

class AuthUser extends Authenticatable
{
    protected $table = 'auth_user';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'username', 'email', 'password', 'role_id',
        'url_photo', 'active', 'last_login', 'created_by', 'updated_by',
        'is_deleted', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'active'    => 'boolean',
        'is_deleted' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Only allow active, non-deleted users to login.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_deleted', false)->where('active', true);
        });
    }

    public function role()
    {
        return $this->belongsTo(AuthRole::class, 'role_id', 'id');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role && str_contains(strtolower($this->role->name), strtolower($roleName));
    }
}
