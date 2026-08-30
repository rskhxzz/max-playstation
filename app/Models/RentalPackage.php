<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RentalPackage extends Model
{
    protected $table = 'c_rental_package';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'name', 'duration_hours', 'price',
        'blocked_start_time', 'blocked_end_time', 'description',
        'is_active', 'created_by', 'updated_by', 'is_deleted',
    ];

    protected $casts = [
        'duration_hours'    => 'integer',
        'price'             => 'float',
        'is_active'         => 'boolean',
        'is_deleted'        => 'boolean',
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
        return $this->hasMany(Booking::class, 'rental_package_id', 'id');
    }

    /**
     * Check if given start time (H:i) is blocked for this package.
     */
    public function isTimeBlocked(string $startTime): bool
    {
        if (!$this->blocked_start_time || !$this->blocked_end_time) {
            return false;
        }

        // Normalize to H:i format (strip seconds if present)
        $normalize = fn(string $t): string => substr($t, 0, 5);

        $startMins   = $this->toMinutes($normalize($startTime));
        $blockedStart = $this->toMinutes($normalize($this->blocked_start_time));

        $blockedEndStr = $normalize($this->blocked_end_time);

        // End at midnight / 00:00 means blocked until end of day
        if ($blockedEndStr === '00:00' || $blockedEndStr === '24:00') {
            return $startMins >= $blockedStart;
        }

        $blockedEnd = $this->toMinutes($blockedEndStr);
        return $startMins >= $blockedStart && $startMins < $blockedEnd;
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = explode(':', $time);
        return ((int)$h) * 60 + ((int)$m);
    }
}
