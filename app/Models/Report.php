<?php

namespace App\Models;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'reason',
        'message',
        'reporter_contact',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reason' => ReportReason::class,
            'status' => ReportStatus::class,
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::NEW);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', ReportStatus::RESOLVED);
    }
}
