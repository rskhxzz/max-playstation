<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $table = 't_booking';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'booking_code', 'customer_id', 'rental_package_id',
        'playstation_unit_id', 'terms_condition_id',
        'rental_start_at', 'rental_end_at',
        'delivery_address', 'google_place_id', 'latitude', 'longitude',
        'distance_km', 'package_price', 'delivery_fee', 'discount_amount',
        'total_amount', 'payment_option', 'initial_payment_amount',
        'total_paid', 'remaining_amount', 'payment_status', 'booking_status',
        'terms_accepted', 'terms_accepted_at',
        'delivery_started_at', 'arrived_at', 'delivered_at',
        'customer_notes', 'cancellation_reason',
        'created_by', 'updated_by', 'is_deleted',
    ];

    protected $casts = [
        'latitude'              => 'float',
        'longitude'             => 'float',
        'distance_km'           => 'float',
        'package_price'         => 'float',
        'delivery_fee'          => 'float',
        'discount_amount'       => 'float',
        'total_amount'          => 'float',
        'initial_payment_amount' => 'float',
        'total_paid'            => 'float',
        'remaining_amount'      => 'float',
        'terms_accepted'        => 'boolean',
        'is_deleted'            => 'boolean',
        'rental_start_at'       => 'datetime',
        'rental_end_at'         => 'datetime',
        'terms_accepted_at'     => 'datetime',
        'delivery_started_at'   => 'datetime',
        'arrived_at'            => 'datetime',
        'delivered_at'          => 'datetime',
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

    // ─── Relationships ────────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function rentalPackage()
    {
        return $this->belongsTo(RentalPackage::class, 'rental_package_id', 'id');
    }

    public function playstationUnit()
    {
        return $this->belongsTo(PlaystationUnit::class, 'playstation_unit_id', 'id');
    }

    public function termsCondition()
    {
        return $this->belongsTo(TermsCondition::class, 'terms_condition_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'id');
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class, 'booking_id', 'id')->latestOfMany();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('booking_status', ['canceled', 'expired', 'delivery_failed']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getBookingStatusLabelAttribute(): string
    {
        return match ($this->booking_status) {
            'pending_payment' => 'Menunggu Pembayaran',
            'delivered'       => 'Siap Diantar',
            'arrived'         => 'Sudah Sampai',
            'completed'       => 'Selesai',
            'canceled'        => 'Dibatalkan',
            'expired'         => 'Kedaluwarsa',
            'delivery_failed' => 'Pengantaran Gagal',
            default           => ucfirst($this->booking_status),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid'   => 'Belum Dibayar',
            'partial'  => 'Sudah DP',
            'paid'     => 'Lunas',
            'refunded' => 'Dikembalikan',
            default    => ucfirst($this->payment_status),
        };
    }

    public function getBookingStatusBadgeAttribute(): string
    {
        return match ($this->booking_status) {
            'pending_payment' => 'warning',
            'delivered'       => 'info',
            'arrived'         => 'success',
            'completed'       => 'primary',
            'canceled'        => 'secondary',
            'expired'         => 'dark',
            'delivery_failed' => 'danger',
            default           => 'secondary',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid'   => 'danger',
            'partial'  => 'warning',
            'paid'     => 'success',
            'refunded' => 'secondary',
            default    => 'secondary',
        };
    }
}
