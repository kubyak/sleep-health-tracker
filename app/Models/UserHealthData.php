<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHealthData extends Model
{
    use HasFactory;

    protected $table = 'user_health_data';

    protected $fillable = [
        'user_id',
        'age',
        'weight',
        'height',
        'gender',
        'blood_type',
        'resting_heart_rate',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'cholesterol_level',
        'glucose_level',
        'sleep_goal',
        'steps_goal',
        'calories_goal',
        'active_minutes_goal',
        'health_conditions',
        'medications',
        'allergies',
        'emergency_contact'
    ];

    protected $casts = [
        'health_conditions' => 'array',
        'medications' => 'array',
        'allergies' => 'array',
        'sleep_goal' => 'integer',
        'steps_goal' => 'integer',
        'calories_goal' => 'integer',
        'active_minutes_goal' => 'integer',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'resting_heart_rate' => 'integer',
        'blood_pressure_systolic' => 'integer',
        'blood_pressure_diastolic' => 'integer',
        'cholesterol_level' => 'decimal:2',
        'glucose_level' => 'decimal:2'
    ];

    /**
     * Отношение к пользователю
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Расчет ИМТ (Индекс массы тела)
     */
    public function getBmiAttribute()
    {
        if ($this->height > 0 && $this->weight > 0) {
            $heightInMeters = $this->height / 100;
            return round($this->weight / ($heightInMeters * $heightInMeters), 1);
        }

        return null;
    }

    /**
     * Категория ИМТ
     */
    public function getBmiCategoryAttribute()
    {
        $bmi = $this->bmi;

        if (!$bmi) return null;

        if ($bmi < 16) return 'severely_underweight';
        if ($bmi < 18.5) return 'underweight';
        if ($bmi < 25) return 'normal';
        if ($bmi < 30) return 'overweight';
        if ($bmi < 35) return 'obese_class_i';
        if ($bmi < 40) return 'obese_class_ii';
        return 'obese_class_iii';
    }

    /**
     * Описание категории ИМТ
     */
    public function getBmiDescriptionAttribute()
    {
        $categories = [
            'severely_underweight' => 'Выраженный дефицит массы тела',
            'underweight' => 'Недостаточная масса тела',
            'normal' => 'Нормальная масса тела',
            'overweight' => 'Избыточная масса тела',
            'obese_class_i' => 'Ожирение I степени',
            'obese_class_ii' => 'Ожирение II степени',
            'obese_class_iii' => 'Ожирение III степени'
        ];

        return $categories[$this->bmi_category] ?? null;
    }

    /**
     * Цвет категории ИМТ для отображения
     */
    public function getBmiColorAttribute()
    {
        $colors = [
            'severely_underweight' => 'danger',
            'underweight' => 'warning',
            'normal' => 'success',
            'overweight' => 'warning',
            'obese_class_i' => 'danger',
            'obese_class_ii' => 'danger',
            'obese_class_iii' => 'danger'
        ];

        return $colors[$this->bmi_category] ?? 'secondary';
    }

    /**
     * Форматированная цель сна
     */
    public function getSleepGoalFormattedAttribute()
    {
        $hours = floor($this->sleep_goal / 60);
        $minutes = $this->sleep_goal % 60;

        if ($minutes > 0) {
            return "{$hours}ч {$minutes}м";
        }
        return "{$hours}ч";
    }

    /**
     * Форматированная цель шагов
     */
    public function getStepsGoalFormattedAttribute()
    {
        return number_format($this->steps_goal);
    }

    /**
     * Форматированная цель калорий
     */
    public function getCaloriesGoalFormattedAttribute()
    {
        return number_format($this->calories_goal);
    }

    /**
     * Форматированная цель активных минут
     */
    public function getActiveMinutesGoalFormattedAttribute()
    {
        return "{$this->active_minutes_goal} мин";
    }

    /**
     * Рекомендуемая норма воды в день (в литрах)
     */
    public function getRecommendedWaterIntakeAttribute()
    {
        if (!$this->weight) return 2.0;

        // 30-40 мл на 1 кг веса
        $waterMl = $this->weight * 35;
        return round($waterMl / 1000, 1);
    }

    /**
     * Базальный метаболизм (BMR) - калории в состоянии покоя
     */
    public function getBasalMetabolicRateAttribute()
    {
        if (!$this->weight || !$this->height || !$this->age || !$this->gender) {
            return null;
        }

        if ($this->gender === 'male') {
            // Формула Миффлина-Сан Жеора для мужчин
            return round(10 * $this->weight + 6.25 * $this->height - 5 * $this->age + 5);
        } else {
            // Формула Миффлина-Сан Жеора для женщин
            return round(10 * $this->weight + 6.25 * $this->height - 5 * $this->age - 161);
        }
    }

    /**
     * Рекомендуемое потребление калорий в день
     */
    public function getRecommendedCalorieIntakeAttribute()
    {
        $bmr = $this->basal_metabolic_rate;
        if (!$bmr) return null;

        // Средний уровень активности (умножаем на 1.55)
        return round($bmr * 1.55);
    }

    /**
     * Артериальное давление в формате строки
     */
    public function getBloodPressureFormattedAttribute()
    {
        if ($this->blood_pressure_systolic && $this->blood_pressure_diastolic) {
            return "{$this->blood_pressure_systolic}/{$this->blood_pressure_diastolic}";
        }
        return null;
    }

    /**
     * Категория артериального давления
     */
    public function getBloodPressureCategoryAttribute()
    {
        if (!$this->blood_pressure_systolic || !$this->blood_pressure_diastolic) {
            return null;
        }

        $systolic = $this->blood_pressure_systolic;
        $diastolic = $this->blood_pressure_diastolic;

        if ($systolic < 120 && $diastolic < 80) return 'normal';
        if ($systolic < 130 && $diastolic < 85) return 'elevated';
        if ($systolic < 140 && $diastolic < 90) return 'hypertension_stage_1';
        if ($systolic < 180 && $diastolic < 120) return 'hypertension_stage_2';
        return 'hypertensive_crisis';
    }

    /**
     * Описание категории давления
     */
    public function getBloodPressureDescriptionAttribute()
    {
        $categories = [
            'normal' => 'Нормальное',
            'elevated' => 'Повышенное',
            'hypertension_stage_1' => 'Гипертония 1 стадии',
            'hypertension_stage_2' => 'Гипертония 2 стадии',
            'hypertensive_crisis' => 'Гипертонический криз'
        ];

        return $categories[$this->blood_pressure_category] ?? null;
    }

    /**
     * Цвет категории давления
     */
    public function getBloodPressureColorAttribute()
    {
        $colors = [
            'normal' => 'success',
            'elevated' => 'warning',
            'hypertension_stage_1' => 'warning',
            'hypertension_stage_2' => 'danger',
            'hypertensive_crisis' => 'danger'
        ];

        return $colors[$this->blood_pressure_category] ?? 'secondary';
    }

    /**
     * Проверка, заполнен ли профиль здоровья
     */
    public function getIsProfileCompleteAttribute()
    {
        $requiredFields = ['age', 'weight', 'height', 'gender'];
        $filledCount = 0;

        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $filledCount++;
            }
        }

        return $filledCount >= 3; // Считаем заполненным если заполнено 3 из 4 основных полей
    }

    /**
     * Процент заполнения профиля
     */
    public function getProfileCompletionPercentageAttribute()
    {
        $fields = [
            'age', 'weight', 'height', 'gender',
            'blood_type', 'resting_heart_rate'
        ];

        $filledCount = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $filledCount++;
            }
        }

        return round(($filledCount / count($fields)) * 100);
    }

    /**
     * Рекомендации на основе данных здоровья
     */
    public function getHealthRecommendationsAttribute()
    {
        $recommendations = [];

        // Рекомендации по ИМТ
        if ($this->bmi && $this->bmi_category !== 'normal') {
            $recommendations[] = 'Ваш ИМТ указывает на ' . strtolower($this->bmi_description) . '. Рекомендуем проконсультироваться со специалистом.';
        }

        // Рекомендации по давлению
        if ($this->blood_pressure_category && $this->blood_pressure_category !== 'normal') {
            $recommendations[] = 'Ваше давление: ' . $this->blood_pressure_description . '. Регулярно отслеживайте показатели.';
        }

        // Рекомендации по пульсу
        if ($this->resting_heart_rate) {
            if ($this->resting_heart_rate > 100) {
                $recommendations[] = 'Повышенный пульс покоя. Рекомендуется консультация врача';
            } elseif ($this->resting_heart_rate < 60) {
                $recommendations[] = 'Низкий пульс покоя. Рекомендуется наблюдение';
            }
        }

        return $recommendations;
    }
}
