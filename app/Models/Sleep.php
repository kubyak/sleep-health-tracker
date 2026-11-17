<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateSleepQuality()
    {
        $durationScore = $this->calculateDurationScore();
        $deepSleepScore = $this->calculateDeepSleepScore();
        $efficiencyScore = $this->calculateEfficiencyScore();
        $consistencyScore = 25; // Базовый балл за консистентность

        return min(100, $durationScore + $deepSleepScore + $efficiencyScore + $consistencyScore);
    }

    private function calculateDurationScore()
    {
        $duration = $this->sleep_duration / 60; // Convert to hours

        if ($duration >= 7 && $duration <= 9) return 25;
        if ($duration >= 6 && $duration < 7) return 20;
        if ($duration >= 5 && $duration < 6) return 15;
        if ($duration >= 4 && $duration < 5) return 10;
        return 5;
    }

    private function calculateDeepSleepScore()
    {
        if (!$this->deep_sleep) return 15;

        $deepSleepPercentage = ($this->deep_sleep / $this->sleep_duration) * 100;

        if ($deepSleepPercentage >= 20) return 25;
        if ($deepSleepPercentage >= 15) return 20;
        if ($deepSleepPercentage >= 10) return 15;
        return 10;
    }

    private function calculateEfficiencyScore()
    {
        if ($this->sleep_duration == 0) return 0;

        $efficiency = (($this->sleep_duration - $this->awake_time) / $this->sleep_duration) * 100;

        if ($efficiency >= 95) return 25;
        if ($efficiency >= 90) return 20;
        if ($efficiency >= 85) return 15;
        if ($efficiency >= 80) return 10;
        return 5;
    }

    public function getSleepPhasesAttribute()
    {
        return [
            'deep' => $this->deep_sleep ?? 0,
            'light' => $this->light_sleep ?? 0,
            'rem' => $this->rem_sleep ?? 0,
            'awake' => $this->awake_time ?? 0
        ];
    }

    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->sleep_duration / 60);
        $minutes = $this->sleep_duration % 60;

        return "{$hours}ч {$minutes}м";
    }

    public function getSleepEfficiencyAttribute()
    {
        if ($this->sleep_duration == 0) return 0;

        return round((($this->sleep_duration - $this->awake_time) / $this->sleep_duration) * 100);
    }
}
