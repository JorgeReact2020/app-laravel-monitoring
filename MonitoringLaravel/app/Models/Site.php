<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'droplet_id',
        'status',
        'notification_phone',
        'timeout',
        'check_interval',
        'metadata',
        'last_checked_at',
        'last_incident_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_checked_at' => 'datetime',
        'last_incident_at' => 'datetime',
    ];

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function rebootLogs(): HasMany
    {
        return $this->hasMany(RebootLog::class);
    }

    public function latestIncident()
    {
        return $this->hasOne(Incident::class)->latestOfMany();
    }

    public function unresolvedIncidents(): HasMany
    {
        return $this->incidents()->whereNull('resolved_at');
    }

    public function isDown(): bool
    {
        return $this->status === 'down';
    }

    public function markAsDown(): void
    {
        $this->update([
            'status' => 'down',
            'last_incident_at' => now(),
        ]);
    }

    public function markAsActive(): void
    {
        $this->update(['status' => 'active']);
    }
}
