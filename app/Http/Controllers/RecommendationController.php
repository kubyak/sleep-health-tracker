<?php

namespace App\Http\Controllers;

use App\Models\HealthRecommendation;
use App\Models\Sleep;
use App\Models\Activity;
use App\Models\UserHealthData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecommendationController extends Controller
{
    public function index()
    {
        $recommendations = HealthRecommendation::active()
            ->ordered()
            ->get()
            ->groupBy('category');

        $personalizedRecommendations = $this->getPersonalizedRecommendations();

        return view('recommendations.index', compact(
            'recommendations',
            'personalizedRecommendations'
        ));
    }

    public function show($category)
    {
        $validCategories = ['sleep', 'activity', 'nutrition', 'mental_health', 'general'];

        if (!in_array($category, $validCategories)) {
            abort(404);
        }

        $recommendations = HealthRecommendation::active()
            ->byCategory($category)
            ->ordered()
            ->get();

        $categoryInfo = $this->getCategoryInfo($category);

        return view('recommendations.category', compact(
            'recommendations',
            'category',
            'categoryInfo'
        ));
    }

    private function getPersonalizedRecommendations()
    {
        $user = auth()->user();
        $personalized = [];

        // Анализ данных сна
        $recentSleep = Sleep::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(7))
            ->get();

        if ($recentSleep->isNotEmpty()) {
            $avgSleepQuality = $recentSleep->avg('sleep_quality');
            $avgSleepDuration = $recentSleep->avg('sleep_duration') / 60;

            if ($avgSleepQuality < 60) {
                $personalized[] = [
                    'type' => 'sleep_quality',
                    'title' => 'Улучшите качество сна',
                    'message' => 'Ваше среднее качество сна составляет ' . round($avgSleepQuality) . '%. Рекомендуем обратить внимание на гигиену сна.',
                    'priority' => 'high',
                    'category' => 'sleep'
                ];
            }

            if ($avgSleepDuration < 6) {
                $personalized[] = [
                    'type' => 'sleep_duration',
                    'title' => 'Увеличьте продолжительность сна',
                    'message' => 'Вы спите в среднем ' . round($avgSleepDuration, 1) . ' часов в сутки. Старайтесь спать 7-9 часов.',
                    'priority' => 'medium',
                    'category' => 'sleep'
                ];
            } elseif ($avgSleepDuration > 9) {
                $personalized[] = [
                    'type' => 'sleep_duration',
                    'title' => 'Обратите внимание на длительность сна',
                    'message' => 'Вы спите в среднем ' . round($avgSleepDuration, 1) . ' часов. Длительный сон может быть признаком усталости.',
                    'priority' => 'low',
                    'category' => 'sleep'
                ];
            }
        }

        // Анализ активности
        $recentActivity = Activity::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(7))
            ->get();

        if ($recentActivity->isNotEmpty()) {
            $avgSteps = $recentActivity->avg('steps');
            $avgActiveMinutes = $recentActivity->avg('active_minutes');

            if ($avgSteps < 5000) {
                $personalized[] = [
                    'type' => 'activity_steps',
                    'title' => 'Увеличьте ежедневную активность',
                    'message' => 'Ваше среднее количество шагов: ' . number_format(round($avgSteps)) . '. Цель - 10,000 шагов в день.',
                    'priority' => 'medium',
                    'category' => 'activity'
                ];
            }

            if ($avgActiveMinutes < 20) {
                $personalized[] = [
                    'type' => 'activity_minutes',
                    'title' => 'Больше активных минут',
                    'message' => 'Вы активны в среднем ' . round($avgActiveMinutes) . ' минут в день. Рекомендуется 30+ минут.',
                    'priority' => 'medium',
                    'category' => 'activity'
                ];
            }
        }

        // Анализ данных здоровья
        $healthData = UserHealthData::where('user_id', $user->id)->first();
        if ($healthData) {
            if ($healthData->bmi && $healthData->bmi_category !== 'normal') {
                $personalized[] = [
                    'type' => 'weight_management',
                    'title' => 'Внимание к весу',
                    'message' => 'Ваш ИМТ указывает на ' . strtolower($healthData->bmi_description) . '. Рекомендуем проконсультироваться со специалистом.',
                    'priority' => 'high',
                    'category' => 'nutrition'
                ];
            }

            if ($healthData->blood_pressure_category &&
                $healthData->blood_pressure_category !== 'normal') {
                $personalized[] = [
                    'type' => 'blood_pressure',
                    'title' => 'Контроль давления',
                    'message' => 'Ваше давление: ' . $healthData->blood_pressure_description . '. Регулярно отслеживайте показатели.',
                    'priority' => 'high',
                    'category' => 'general'
                ];
            }
        }

        // Сортировка по приоритету
        usort($personalized, function($a, $b) {
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            return $priorityOrder[$b['priority']] - $priorityOrder[$a['priority']];
        });

        return $personalized;
    }

    private function getCategoryInfo($category)
    {
        $info = [
            'sleep' => [
                'title' => 'Сон и отдых',
                'description' => 'Рекомендации по улучшению качества и продолжительности сна',
                'icon' => 'fas fa-bed',
                'color' => 'primary'
            ],
            'activity' => [
                'title' => 'Физическая активность',
                'description' => 'Советы по поддержанию физической формы и активности',
                'icon' => 'fas fa-running',
                'color' => 'success'
            ],
            'nutrition' => [
                'title' => 'Питание',
                'description' => 'Рекомендации по здоровому питанию и водному балансу',
                'icon' => 'fas fa-apple-alt',
                'color' => 'warning'
            ],
            'mental_health' => [
                'title' => 'Ментальное здоровье',
                'description' => 'Советы по управлению стрессом и психологическому благополучию',
                'icon' => 'fas fa-spa',
                'color' => 'info'
            ],
            'general' => [
                'title' => 'Общие рекомендации',
                'description' => 'Общие советы по поддержанию здоровья и wellness',
                'icon' => 'fas fa-heart',
                'color' => 'danger'
            ]
        ];

        return $info[$category] ?? $info['general'];
    }

    public function getDailyTip()
    {
        $tip = HealthRecommendation::active()
            ->inRandomOrder()
            ->first();

        if (!$tip) {
            // Fallback советы
            $fallbackTips = [
                [
                    'title' => 'Регулярные физические упражнения',
                    'description' => 'Старайтесь уделять хотя бы 30 минут в день физической активности для поддержания здоровья',
                    'category' => 'Физическая активность',
                    'icon' => 'fas fa-running'
                ],
                [
                    'title' => 'Качественный сон',
                    'description' => 'Спите 7-9 часов в сутки для полноценного восстановления организма',
                    'category' => 'Сон',
                    'icon' => 'fas fa-bed'
                ],
                [
                    'title' => 'Сбалансированное питание',
                    'description' => 'Включайте в рацион больше овощей, фруктов и цельнозерновых продуктов',
                    'category' => 'Питание',
                    'icon' => 'fas fa-apple-alt'
                ],
                [
                    'title' => 'Пейте воду',
                    'description' => 'Выпивайте 2 литра воды в день для поддержания водного баланса',
                    'category' => 'Питание',
                    'icon' => 'fas fa-tint'
                ],
                [
                    'title' => 'Медитация и отдых',
                    'description' => 'Найдите время для расслабления и медитации чтобы снизить стресс',
                    'category' => 'Ментальное здоровье',
                    'icon' => 'fas fa-spa'
                ]
            ];

            $randomTip = $fallbackTips[array_rand($fallbackTips)];

            return response()->json([
                'title' => $randomTip['title'],
                'description' => $randomTip['description'],
                'category' => $randomTip['category'],
                'icon' => $randomTip['icon']
            ]);
        }

        return response()->json([
            'title' => $tip->title,
            'description' => $tip->description,
            'category' => $tip->formatted_category,
            'icon' => $tip->icon
        ]);
    }

    public function markAsRead(Request $request, $recommendationId)
    {
        // Здесь можно добавить логику отметки прочитанных рекомендаций
        // Например, сохранение в таблицу user_read_recommendations

        return response()->json(['success' => true]);
    }
}
