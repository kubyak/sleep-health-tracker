<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sleep extends Model
{
    use HasFactory;

    protected $table = 'sleep_records';

    protected $fillable = [
        'user_id',
        'date',
        'bedtime',
        'wakeup_time',
        'sleep_duration',
        'deep_sleep',
        'light_sleep',
        'rem_sleep',
        'awake_time',
        'sleep_quality',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'bedtime' => 'datetime',
        'wakeup_time' => 'datetime',
    ];

    protected $appends = [
        'sleep_phases',
        'formatted_duration',
        'sleep_efficiency',
        'sleep_score',
        'duration_hours'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* Расчёт общего балла качества сна */
    public function calculateSleepQuality()
    {
        $durationScore = $this->calculateDurationScore();           // Баллы за продолжительность сна
        $deepSleepScore = $this->calculateDeepSleepScore();        // Баллы за глубину сна
        $efficiencyScore = $this->calculateEfficiencyScore();      // Баллы за эффективность сна
        $consistencyScore = 15;                                    // Фиксированный бонус за стабильность сна

        return min(100, $durationScore + $deepSleepScore + $efficiencyScore + $consistencyScore);
    }

    /* Очки за продолжительность сна */
    private function calculateDurationScore()
    {
        $duration = $this->sleep_duration / 60;                     // Длительность сна в часах

        if ($duration >= 7 && $duration <= 9) return 25;          // Идеальная продолжительность сна
        if ($duration >= 6 && $duration < 7) return 20;           // Небольшое отклонение
        if ($duration >= 5 && $duration < 6) return 15;           // Умеренно низкое
        if ($duration >= 4 && $duration < 5) return 10;           // Недостаточный сон
        return 5;                                                  // Очень короткий сон
    }

    /* Очки за глубокий сон */
    private function calculateDeepSleepScore()
    {
        if (!$this->deep_sleep) return 10;                         // Нет данных о глубоком сне

        $deepSleepPercentage = ($this->deep_sleep / $this->sleep_duration) * 100;

        if ($deepSleepPercentage >= 20) return 25;                 // Отличный глубокий сон
        if ($deepSleepPercentage >= 15) return 20;                 // Нормальный глубокий сон
        if ($deepSleepPercentage >= 10) return 15;                 // Низкий уровень глубокого сна
        return 10;                                                 // Крайне низкий глубокий сон
    }

    /* Очки за эффективность сна */
    private function calculateEfficiencyScore()
    {
        if ($this->sleep_duration == 0) return 0;

        $efficiency = (($this->sleep_duration - $this->awake_time) / $this->sleep_duration) * 100;

        if ($efficiency >= 95) return 25;                          // Высокая эффективность
        if ($efficiency >= 90) return 20;                          // Средняя высокая эффективность
        if ($efficiency >= 85) return 15;                          // Средний уровень эффективности
        if ($efficiency >= 80) return 10;                          // Нехватка эффективности
        return 5;                                                   // Чрезвычайно низкая эффективность
    }

    /* Атрибуты доступа для удобства представления данных */
    public function getSleepPhasesAttribute()
    {
        return [
            'deep_sleep' => $this->deep_sleep ?? 0,
            'light_sleep' => $this->light_sleep ?? 0,
            'rem_sleep' => $this->rem_sleep ?? 0,
            'awake_time' => $this->awake_time ?? 0
        ];
    }

    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->sleep_duration / 60);
        $minutes = $this->sleep_duration % 60;

        if ($minutes > 0) {
            return "{$hours}ч {$minutes}м";
        }

        return "{$hours}ч";
    }

    public function getSleepEfficiencyAttribute()
    {
        if ($this->sleep_duration == 0) return 0;

        return round((($this->sleep_duration - $this->awake_time) / $this->sleep_duration) * 100);
    }

    public function getSleepScoreAttribute()
    {
        return $this->sleep_quality ?? $this->calculateSleepQuality();
    }

    public function getDurationHoursAttribute()
    {
        return round($this->sleep_duration / 60, 1);
    }

    public function getPhasePercentage($phase)
    {
        if ($this->sleep_duration == 0) return 0;

        $phaseTime = match($phase) {
            'deep_sleep' => $this->deep_sleep,
            'light_sleep' => $this->light_sleep,
            'rem_sleep' => $this->rem_sleep,
            'awake_time' => $this->awake_time,
            default => 0
        };

        return round(($phaseTime / $this->sleep_duration) * 100, 1);
    }

    public function getQualityLevelAttribute()
    {
        $quality = $this->sleep_quality;

        if ($quality >= 80) return 'excellent';                   // Отличный сон
        if ($quality >= 60) return 'good';                        // Хороший сон
        if ($quality >= 40) return 'fair';                        // Среднее качество
        return 'poor';                                             // Плохой сон
    }

    public function getQualityBadgeClassAttribute()
    {
        return match($this->quality_level) {
            'excellent' => 'bg-success',
            'good' => 'bg-info',
            'fair' => 'bg-warning',
            'poor' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getQualityTextAttribute()
    {
        return match($this->quality_level) {
            'excellent' => 'Отличный',
            'good' => 'Хороший',
            'fair' => 'Средний',
            'poor' => 'Плохой',
            default => 'Неизвестно'
        };
    }

    public function getTimeRangeAttribute()
    {
        $bedtime = Carbon::parse($this->bedtime)->format('H:i');
        $wakeup = Carbon::parse($this->wakeup_time)->format('H:i');

        return "{$bedtime} - {$wakeup}";
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->translatedFormat('d F Y');
    }

    public function getShortDateAttribute()
    {
        return $this->date->format('d.m.Y');
    }

    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    public function scopeHighQuality($query, $threshold = 80)
    {
        return $query->where('sleep_quality', '>=', $threshold);
    }

    public function scopeLowQuality($query, $threshold = 60)
    {
        return $query->where('sleep_quality', '<', $threshold);
    }

    public function isOptimalDuration()
    {
        $hours = $this->sleep_duration / 60;
        return $hours >= 7 && $hours <= 9;
    }

    public function isHighEfficiency()
    {
        return $this->sleep_efficiency >= 85;
    }

    public function hasGoodDeepSleep()
    {
        if (!$this->deep_sleep) return false;

        $percentage = ($this->deep_sleep / $this->sleep_duration) * 100;
        return $percentage >= 15;
    }

    public function getRecommendations()
    {
        $recommendations = [];

        // Рекомендация по продолжительности сна
        $hours = $this->sleep_duration / 60;
        if ($hours < 7) {
            $recommendations[] = 'Старайтесь спать не менее 7 часов.';
        } elseif ($hours > 9) {
            $recommendations[] = 'Сон дольше 9 часов может негативно сказываться на здоровье.';
        }

        // Рекомендация по эффективному отдыху
        if ($this->sleep_efficiency < 85) {
            $recommendations[] = 'Повысьте свою эффективность сна, устраняя раздражители перед сном.';
        }

        // Рекомендация по глубине сна
        if (!$this->hasGoodDeepSleep()) {
            $recommendations[] = 'Увеличьте долю глубокого сна путём регулярных физических нагрузок и снижения стресса.';
        }

        return $recommendations;
    }
}