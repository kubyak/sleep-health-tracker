<?php

namespace App\Http\Controllers;

use App\Models\Sleep;
use App\Models\Activity;
use App\Models\HealthRecommendation;
use App\Models\UserHealthData;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');
        $weekAgo = now()->subDays(6)->format('Y-m-d');

        // Данные сна
        $sleepToday = Sleep::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $sleepWeek = Sleep::where('user_id', $user->id)
            ->whereBetween('date', [$weekAgo, $today])
            ->orderBy('date')
            ->get();

        // Данные активности
        $activityToday = Activity::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $activityWeek = Activity::where('user_id', $user->id)
            ->whereBetween('date', [$weekAgo, $today])
            ->orderBy('date')
            ->get();

        // Рекомендации
        $recommendations = HealthRecommendation::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->take(4)
            ->get();

        // Статистика
        $sleepStats = $this->getSleepStats($sleepWeek);
        $activityStats = $this->getActivityStats($activityWeek);
        $healthData = $this->getHealthData($user);

        // Еженедельный прогресс
        $weeklyProgress = $this->getWeeklyProgress($user, $weekAgo, $today);

        return view('dashboard', compact(
            'sleepToday',
            'sleepWeek',
            'activityToday',
            'activityWeek',
            'recommendations',
            'sleepStats',
            'activityStats',
            'healthData',
            'weeklyProgress'
        ));
    }

    private function getSleepStats($sleepWeek)
    {
        if ($sleepWeek->isEmpty()) {
            return [
                'avg_duration' => 0,
                'avg_quality' => 0,
                'consistency' => 0,
                'total_sleep' => 0,
                'best_night' => null
            ];
        }

        $bestNight = $sleepWeek->sortByDesc('sleep_quality')->first();

        return [
            'avg_duration' => round($sleepWeek->avg('sleep_duration') / 60, 1),
            'avg_quality' => round($sleepWeek->avg('sleep_quality')),
            'consistency' => round(($sleepWeek->count() / 7) * 100),
            'total_sleep' => round($sleepWeek->sum('sleep_duration') / 60, 1),
            'best_night' => $bestNight
        ];
    }

    private function getActivityStats($activityWeek)
    {
        if ($activityWeek->isEmpty()) {
            return [
                'avg_steps' => 0,
                'avg_calories' => 0,
                'active_days' => 0,
                'total_steps' => 0,
                'total_distance' => 0
            ];
        }

        return [
            'avg_steps' => round($activityWeek->avg('steps')),
            'avg_calories' => round($activityWeek->avg('calories_burned')),
            'active_days' => $activityWeek->where('steps', '>=', 5000)->count(),
            'total_steps' => $activityWeek->sum('steps'),
            'total_distance' => round($activityWeek->sum('distance'), 1)
        ];
    }

    private function getHealthData($user)
    {
        $healthData = UserHealthData::where('user_id', $user->id)->first();

        if (!$healthData) {
            $healthData = new UserHealthData();
            $healthData->user_id = $user->id;
            $healthData->save();
        }

        return $healthData;
    }

    private function getWeeklyProgress($user, $startDate, $endDate)
    {
        // Прогресс по сну
        $sleepProgress = Sleep::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('AVG(sleep_quality) as avg_quality'),
                DB::raw('AVG(sleep_duration) as avg_duration'),
                DB::raw('COUNT(*) as days_recorded')
            )
            ->first();

        // Прогресс по активности
        $activityProgress = Activity::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('AVG(steps) as avg_steps'),
                DB::raw('AVG(calories_burned) as avg_calories'),
                DB::raw('AVG(active_minutes) as avg_minutes'),
                DB::raw('COUNT(*) as days_recorded')
            )
            ->first();

        return [
            'sleep' => [
                'quality' => round($sleepProgress->avg_quality ?? 0),
                'duration' => round(($sleepProgress->avg_duration ?? 0) / 60, 1),
                'consistency' => round((($sleepProgress->days_recorded ?? 0) / 7) * 100)
            ],
            'activity' => [
                'steps' => round($activityProgress->avg_steps ?? 0),
                'calories' => round($activityProgress->avg_calories ?? 0),
                'minutes' => round($activityProgress->avg_minutes ?? 0),
                'consistency' => round((($activityProgress->days_recorded ?? 0) / 7) * 100)
            ]
        ];
    }

    public function getChartData(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'week');

        if ($period === 'month') {
            $startDate = now()->subDays(30)->format('Y-m-d');
            $groupFormat = 'Y-m-d';
        } else {
            $startDate = now()->subDays(6)->format('Y-m-d');
            $groupFormat = 'Y-m-d';
        }

        // Данные сна для графика
        $sleepData = Sleep::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get()
            ->groupBy(function($item) use ($groupFormat) {
                return Carbon::parse($item->date)->format($groupFormat);
            })
            ->map(function($group) {
                return [
                    'quality' => round($group->avg('sleep_quality')),
                    'duration' => round($group->avg('sleep_duration') / 60, 1)
                ];
            });

        // Данные активности для графика
        $activityData = Activity::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get()
            ->groupBy(function($item) use ($groupFormat) {
                return Carbon::parse($item->date)->format($groupFormat);
            })
            ->map(function($group) {
                return [
                    'steps' => round($group->avg('steps')),
                    'calories' => round($group->avg('calories_burned'))
                ];
            });

        return response()->json([
            'sleep' => $sleepData,
            'activity' => $activityData,
            'labels' => array_keys($sleepData->toArray())
        ]);
    }
}
