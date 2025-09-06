<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'status',
        'error_details',
        'response_time',
        'status_code',
        'detected_at',
        'verified_at',
        'notification_sent_at',
        'resolved_at',
        'verification_attempts',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'verified_at' => 'datetime',
        'notification_sent_at' => 'datetime',
        'resolved_at' => 'datetime',
        'verification_attempts' => 'array',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function rebootLogs(): HasMany
    {
        return $this->hasMany(RebootLog::class);
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function markAsVerified(): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    public function markAsNotificationSent(): void
    {
        $this->update([
            'status' => 'notification_sent',
            'notification_sent_at' => now(),
        ]);
    }

    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $this->site->markAsActive();
    }

    public function addVerificationAttempt(array $attempt): void
    {
        $attempts = $this->verification_attempts ?? [];
        $attempts[] = $attempt;

        $this->update(['verification_attempts' => $attempts]);
    }
}
