<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DeliveryRate extends Model
{
    protected $table = 'c_delivery_rate';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'minimum_distance_km', 'maximum_distance_km',
        'delivery_fee', 'is_active',
        'created_by', 'updated_by', 'is_deleted',
    ];

    protected $casts = [
        'minimum_distance_km' => 'float',
        'maximum_distance_km' => 'float',
        'delivery_fee'        => 'float',
        'is_active'           => 'boolean',
        'is_deleted'          => 'boolean',
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
}
