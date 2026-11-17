<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activity_records';

    protected $fillable = [
        'user_id',
        'date',
        'steps',
        'calories_burned',
        'active_minutes',
        'distance',
        'heart_rate',
        'workout_type'
    ];

    protected $casts = [
        'date' => 'date',
        'distance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivityLevel()
    {
        if ($this->steps >= 10000) return 'excellent';
        if ($this->steps >= 7500) return 'good';
        if ($this->steps >= 5000) return 'moderate';
        return 'low';
    }

    public function getActivityScore()
    {
        $stepsScore = $this->calculateStepsScore();
        $caloriesScore = $this->calculateCaloriesScore();
        $minutesScore = $this->calculateMinutesScore();

        return min(100, $stepsScore + $caloriesScore + $minutesScore);
    }

    private function calculateStepsScore()
    {
        if ($this->steps >= 10000) return 40;
        if ($this->steps >= 7500) return 30;
        if ($this->steps >= 5000) return 20;
        if ($this->steps >= 2500) return 10;
        return 5;
    }

    private function calculateCaloriesScore()
    {
        if ($this->calories_burned >= 500) return 30;
        if ($this->calories_burned >= 300) return 20;
        if ($this->calories_burned >= 150) return 15;
        if ($this->calories_burned >= 50) return 10;
        return 5;
    }

    private function calculateMinutesScore()
    {
        if ($this->active_minutes >= 60) return 30;
        if ($this->active_minutes >= 45) return 25;
        if ($this->active_minutes >= 30) return 20;
        if ($this->active_minutes >= 15) return 15;
        return 10;
    }

    public function getPaceAttribute()
    {
        if ($this->distance > 0 && $this->active_minutes > 0) {
            $pace = $this->active_minutes / $this->distance;
            $minutes = floor($pace);
            $seconds = round(($pace - $minutes) * 60);
            return "{$minutes}:".str_pad($seconds, 2, '0', STR_PAD_LEFT);
        }

        return null;
    }

    public function getWorkoutTypeIconAttribute()
    {
        $icons = [
            'walking' => 'fas fa-walking',
            'running' => 'fas fa-running',
            'cycling' => 'fas fa-bicycle',
            'swimming' => 'fas fa-swimmer',
            'yoga' => 'fas fa-spa',
            'gym' => 'fas fa-dumbbell',
            'other' => 'fas fa-heartbeat'
        ];

        return $icons[$this->workout_type] ?? 'fas fa-heartbeat';
    }

    public function getFormattedDistanceAttribute()
    {
        return number_format($this->distance, 1) . ' км';
    }

    public function getFormattedStepsAttribute()
    {
        return number_format($this->steps);
    }
}
