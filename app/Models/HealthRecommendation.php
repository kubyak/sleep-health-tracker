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
        'is_active'
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean'
    ];

    public function getTipsArrayAttribute()
    {
        return $this->tips ? explode('|', $this->tips) : [];
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
}
