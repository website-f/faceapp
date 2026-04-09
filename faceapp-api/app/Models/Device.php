<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'device_key',
        'last_ip',
        'last_version',
        'person_count',
        'face_count',
        'free_disk_space',
        'last_seen_at',
        'last_record_at',
        'last_heartbeat_payload',
        'last_record_payload',
    ];

    protected $appends = [
        'is_online',
    ];

    protected function casts(): array
    {
        return [
            'person_count' => 'integer',
            'face_count' => 'integer',
            'last_seen_at' => 'datetime',
            'last_record_at' => 'datetime',
            'last_heartbeat_payload' => 'array',
            'last_record_payload' => 'array',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(DeviceEvent::class, 'device_key', 'device_key');
    }

    public function getIsOnlineAttribute(): bool
    {
        if (! $this->last_seen_at) {
            return false;
        }

        $window = (int) config('gateway.monitoring.online_window_seconds', 180);

        return $this->last_seen_at->greaterThanOrEqualTo(now()->subSeconds($window));
    }
}
