<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthRole extends Model
{
    protected $table = 'auth_role';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'description'];

    public function users()
    {
        return $this->hasMany(AuthUser::class, 'role_id', 'id');
    }
}
