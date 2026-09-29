<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliveryRate extends Model
{
    protected $table = 'c_delivery_rate';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'minimum_distance_km',
        'maximum_distance_km',
        'delivery_fee',
        'driver_fee',
        'company_fuel_deduction',
        'is_active',
        'created_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'minimum_distance_km' => 'float',
        'maximum_distance_km' => 'float',
        'delivery_fee' => 'float',
        'driver_fee' => 'float',
        'company_fuel_deduction' => 'float',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(
            'not_deleted',
            fn(Builder $builder) => $builder->where('is_deleted', false)
        );

        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function getCompanyDriverIncomeAttribute(): float
    {
        return max(
            0,
            (float) $this->driver_fee
                - (float) $this->company_fuel_deduction
        );
    }
}
