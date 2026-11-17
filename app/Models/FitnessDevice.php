<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitnessDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_type',
        'device_name',
        'api_provider',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'is_connected',
        'last_sync_at',
        'sync_settings',
        'raw_data'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'is_connected' => 'boolean',
        'sync_settings' => 'array',
        'raw_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Проверяет, нужно ли обновить токен
     */
    public function needsTokenRefresh()
    {
        return $this->token_expires_at &&
            $this->token_expires_at->subMinutes(30)->isPast();
    }

    /**
     * Проверяет, активно ли подключение
     */
    public function isActive()
    {
        return $this->is_connected &&
            $this->access_token &&
            !$this->needsTokenRefresh();
    }

    /**
     * Получает настройки синхронизации
     */
    public function getSyncSettingsAttribute($value)
    {
        $defaults = [
            'sync_sleep' => true,
            'sync_activity' => true,
            'sync_heart_rate' => true,
            'sync_weight' => false,
            'auto_sync' => true,
            'sync_interval' => 60 // minutes
        ];

        $settings = $value ? json_decode($value, true) : [];

        return array_merge($defaults, $settings);
    }
}
