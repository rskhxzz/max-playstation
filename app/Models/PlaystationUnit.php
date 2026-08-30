<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PlaystationUnit extends Model
{
    protected $table = 'c_playstation_unit';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'unit_code', 'name', 'console_type', 'serial_number',
        'status', 'notes', 'is_active',
        'created_by', 'updated_by', 'is_deleted',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_deleted' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', fn(Builder $b) => $b->where('is_deleted', false));

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'playstation_unit_id', 'id');
    }
}
