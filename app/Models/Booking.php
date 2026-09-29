<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $table = 't_booking';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'booking_code',
        'customer_id',
        'rental_package_id',
        'playstation_unit_id',
        'terms_condition_id',
        'rental_start_at',
        'rental_end_at',
        'delivery_address',
        'google_place_id',
        'latitude',
        'longitude',
        'distance_km',
        'package_price',
        'delivery_fee',
        'driver_fee',
        'fuel_deduction',
        'driver_income',
        'discount_amount',
        'total_amount',
        'payment_option',
        'initial_payment_amount',
        'total_paid',
        'remaining_amount',
        'payment_status',
        'booking_status',
        'terms_accepted',
        'terms_accepted_at',
        'delivery_started_at',
        'arrived_at',
        'delivered_at',
        'driver_id',
        'vehicle_type',
        'driver_assigned_at',
        'delivery_photo_path',
        'delivery_photo_taken_at',
        'pickup_started_at',
        'picked_up_at',
        'customer_notes',
        'cancellation_reason',
        'created_by',
        'updated_by',
        'is_deleted',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_km' => 'float',
        'package_price' => 'float',
        'delivery_fee' => 'float',
        'driver_fee' => 'float',
        'fuel_deduction' => 'float',
        'driver_income' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'initial_payment_amount' => 'float',
        'total_paid' => 'float',
        'remaining_amount' => 'float',
        'terms_accepted' => 'boolean',
        'is_deleted' => 'boolean',
        'rental_start_at' => 'datetime',
        'rental_end_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'delivery_started_at' => 'datetime',
        'driver_assigned_at' => 'datetime',
        'arrived_at' => 'datetime',
        'delivered_at' => 'datetime',
        'delivery_photo_taken_at' => 'datetime',
        'pickup_started_at' => 'datetime',
        'picked_up_at' => 'datetime',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function rentalPackage()
    {
        return $this->belongsTo(
            RentalPackage::class,
            'rental_package_id',
            'id'
        );
    }

    public function playstationUnit()
    {
        return $this->belongsTo(
            PlaystationUnit::class,
            'playstation_unit_id',
            'id'
        );
    }

    public function driver()
    {
        return $this->belongsTo(
            AuthUser::class,
            'driver_id',
            'id'
        )->withoutGlobalScope('active');
    }

    public function termsCondition()
    {
        return $this->belongsTo(
            TermsCondition::class,
            'terms_condition_id',
            'id'
        );
    }

    public function payments()
    {
        return $this->hasMany(
            Payment::class,
            'booking_id',
            'id'
        );
    }

    public function latestPayment()
    {
        return $this->hasOne(
            Payment::class,
            'booking_id',
            'id'
        )->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('booking_status', [
            'canceled',
            'expired',
            'delivery_failed',
        ]);
    }

    public function scopeAssignedToDriver(
        Builder $query,
        string $driverId
    ): Builder {
        return $query->where('driver_id', $driverId);
    }

    public function isAssignedTo(string $driverId): bool
    {
        return (string) $this->driver_id === $driverId;
    }

    public function getBookingStatusLabelAttribute(): string
    {
        if ($this->booking_status === 'delivered') {
            return $this->driver_id
                ? 'Ditugaskan'
                : 'Siap Diantar';
        }

        return match ($this->booking_status) {
            'pending_payment' => 'Menunggu Pembayaran',
            'on_delivery' => 'Sedang Diantar',
            'arrived' => 'Sudah Sampai',
            'completed' => 'Selesai',
            'canceled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa',
            'delivery_failed' => 'Pengantaran Gagal',
            default => ucfirst((string) $this->booking_status),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Belum Dibayar',
            'partial' => 'Sudah DP',
            'paid' => 'Lunas',
            'refunded' => 'Dikembalikan',
            default => ucfirst((string) $this->payment_status),
        };
    }

    public function getBookingStatusBadgeAttribute(): string
    {
        if ($this->booking_status === 'delivered') {
            return $this->driver_id
                ? 'secondary'
                : 'info';
        }

        return match ($this->booking_status) {
            'pending_payment' => 'warning',
            'arrived' => 'success',
            'on_delivery' => 'info',
            'completed' => 'primary',
            'canceled' => 'secondary',
            'expired' => 'dark',
            'delivery_failed' => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'danger',
            'partial' => 'warning',
            'paid' => 'success',
            'refunded' => 'secondary',
            default => 'secondary',
        };
    }
}
