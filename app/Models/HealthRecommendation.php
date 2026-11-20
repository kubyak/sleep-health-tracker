<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'title',
        'description',
        'tips',
        'icon',
        'priority',
        'is_active',
        'min_sleep_quality',
        'max_sleep_quality',
        'min_activity_level',
        'max_activity_level'
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
        'min_sleep_quality' => 'integer',
        'max_sleep_quality' => 'integer',
        'min_activity_level' => 'integer',
        'max_activity_level' => 'integer'
    ];

    protected $appends = [
        'tips_array',
        'category_color',
        'category_icon',
        'formatted_category',
        'priority_text'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 7);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc');
    }

    public function scopeForSleepQuality($query, $quality)
    {
        return $query->where(function($q) use ($quality) {
            $q->whereNull('min_sleep_quality')
                ->orWhere('min_sleep_quality', '<=', $quality);
        })->where(function($q) use ($quality) {
            $q->whereNull('max_sleep_quality')
                ->orWhere('max_sleep_quality', '>=', $quality);
        });
    }

    public function scopeForActivityLevel($query, $activityLevel)
    {
        return $query->where(function($q) use ($activityLevel) {
            $q->whereNull('min_activity_level')
                ->orWhere('min_activity_level', '<=', $activityLevel);
        })->where(function($q) use ($activityLevel) {
            $q->whereNull('max_activity_level')
                ->orWhere('max_activity_level', '>=', $activityLevel);
        });
    }

    // Accessors
    public function getTipsArrayAttribute()
    {
        if (!$this->tips) {
            return [];
        }

        // Поддерживаем оба формата: через | и через запятую
        if (str_contains($this->tips, '|')) {
            return array_filter(array_map('trim', explode('|', $this->tips)));
        } else {
            return array_filter(array_map('trim', explode(',', $this->tips)));
        }
    }

    public function getCategoryColorAttribute()
    {
        $colors = [
            'sleep' => 'primary',
            'activity' => 'success',
            'nutrition' => 'warning',
            'mental_health' => 'info',
            'general' => 'secondary'
        ];

        return $colors[$this->category] ?? 'secondary';
    }

    public function getCategoryIconAttribute()
    {
        $icons = [
            'sleep' => 'fas fa-bed',
            'activity' => 'fas fa-running',
            'nutrition' => 'fas fa-apple-alt',
            'mental_health' => 'fas fa-spa',
            'general' => 'fas fa-heart'
        ];

        return $icons[$this->category] ?? 'fas fa-heart';
    }

    public function getFormattedCategoryAttribute()
    {
        $categories = [
            'sleep' => 'Сон',
            'activity' => 'Активность',
            'nutrition' => 'Питание',
            'mental_health' => 'Ментальное здоровье',
            'general' => 'Общее'
        ];

        return $categories[$this->category] ?? $this->category;
    }

    public function getPriorityTextAttribute()
    {
        if ($this->priority >= 8) return 'Высокий';
        if ($this->priority >= 5) return 'Средний';
        return 'Низкий';
    }

    public function getIsHighPriorityAttribute()
    {
        return $this->priority >= 8;
    }

    public function getIsMediumPriorityAttribute()
    {
        return $this->priority >= 5 && $this->priority < 8;
    }

    public function getIsLowPriorityAttribute()
    {
        return $this->priority < 5;
    }

    // Методы для персонализации
    public function isRelevantForUser($userSleepQuality = null, $userActivityLevel = null)
    {
        // Проверка качества сна
        if ($userSleepQuality !== null) {
            if ($this->min_sleep_quality !== null && $userSleepQuality < $this->min_sleep_quality) {
                return false;
            }
            if ($this->max_sleep_quality !== null && $userSleepQuality > $this->max_sleep_quality) {
                return false;
            }
        }

        // Проверка уровня активности
        if ($userActivityLevel !== null) {
            if ($this->min_activity_level !== null && $userActivityLevel < $this->min_activity_level) {
                return false;
            }
            if ($this->max_activity_level !== null && $userActivityLevel > $this->max_activity_level) {
                return false;
            }
        }

        return true;
    }

    public function getRelevanceScore($userSleepQuality = null, $userActivityLevel = null)
    {
        $score = $this->priority;

        // Бонус за соответствие данным пользователя
        if ($userSleepQuality !== null && $this->min_sleep_quality !== null && $this->max_sleep_quality !== null) {
            if ($userSleepQuality >= $this->min_sleep_quality && $userSleepQuality <= $this->max_sleep_quality) {
                $score += 2;
            }
        }

        if ($userActivityLevel !== null && $this->min_activity_level !== null && $this->max_activity_level !== null) {
            if ($userActivityLevel >= $this->min_activity_level && $userActivityLevel <= $this->max_activity_level) {
                $score += 2;
            }
        }

        return $score;
    }

    // Статические методы для быстрого доступа к рекомендациям
    public static function getRandomTip()
    {
        return self::active()->inRandomOrder()->first();
    }

    public static function getPersonalizedRecommendations($userSleepQuality = null, $userActivityLevel = null)
    {
        return self::active()
            ->ordered()
            ->get()
            ->filter(function($recommendation) use ($userSleepQuality, $userActivityLevel) {
                return $recommendation->isRelevantForUser($userSleepQuality, $userActivityLevel);
            })
            ->sortByDesc(function($recommendation) use ($userSleepQuality, $userActivityLevel) {
                return $recommendation->getRelevanceScore($userSleepQuality, $userActivityLevel);
            });
    }

    public static function getByCategoryWithRelevance($category, $userSleepQuality = null, $userActivityLevel = null)
    {
        return self::active()
            ->byCategory($category)
            ->ordered()
            ->get()
            ->filter(function($recommendation) use ($userSleepQuality, $userActivityLevel) {
                return $recommendation->isRelevantForUser($userSleepQuality, $userActivityLevel);
            })
            ->sortByDesc(function($recommendation) use ($userSleepQuality, $userActivityLevel) {
                return $recommendation->getRelevanceScore($userSleepQuality, $userActivityLevel);
            });
    }
}
