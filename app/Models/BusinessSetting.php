<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BusinessSetting extends Model
{
    protected $table = 'c_business_setting';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'business_name', 'phone_number', 'address',
        'google_place_id', 'latitude', 'longitude',
        'down_payment_amount', 'payment_expiry_minutes',
        'maximum_delivery_km', 'is_active',
        'created_by', 'updated_by', 'is_deleted',
    ];

    protected $casts = [
        'latitude'              => 'float',
        'longitude'             => 'float',
        'down_payment_amount'   => 'float',
        'payment_expiry_minutes' => 'integer',
        'maximum_delivery_km'   => 'float',
        'is_active'             => 'boolean',
        'is_deleted'            => 'boolean',
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

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
