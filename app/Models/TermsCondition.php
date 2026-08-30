<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TermsCondition extends Model
{
    protected $table = 'c_terms_condition';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'title', 'content', 'version', 'is_active',
        'created_by', 'updated_by', 'is_deleted',
    ];

    protected $casts = [
        'version'    => 'integer',
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
        return $this->hasMany(Booking::class, 'terms_condition_id', 'id');
    }
}
