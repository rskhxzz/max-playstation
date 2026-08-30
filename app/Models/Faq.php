<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Faq extends Model
{
    protected $table = 'c_faq';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'question', 'answer', 'seq', 'is_published',
        'created_by', 'updated_by', 'is_deleted',
    ];

    protected $casts = [
        'seq'          => 'integer',
        'is_published' => 'boolean',
        'is_deleted'   => 'boolean',
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
