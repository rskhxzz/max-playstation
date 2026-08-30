<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $table = 't_payment';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'booking_id', 'payment_code', 'payment_type', 'payment_method',
        'provider', 'external_payment_id', 'external_reference_id',
        'requested_amount', 'paid_amount', 'status',
        'qr_string', 'qr_url', 'expires_at', 'paid_at',
        'created_by', 'updated_by', 'is_deleted',
    ];

    protected $casts = [
        'requested_amount' => 'float',
        'paid_amount'      => 'float',
        'is_deleted'       => 'boolean',
        'expires_at'       => 'datetime',
        'paid_at'          => 'datetime',
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

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu',
            'succeeded' => 'Berhasil',
            'expired'   => 'Kedaluwarsa',
            'failed'    => 'Gagal',
            'refunded'  => 'Dikembalikan',
            default     => ucfirst($this->status),
        };
    }
}
