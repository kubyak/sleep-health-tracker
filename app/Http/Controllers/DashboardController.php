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
            $groupFormat = 'd.m';
        } else {
            $startDate = now()->subDays(6)->format('Y-m-d');
            $groupFormat = 'd.m';
        }

        // Данные сна для графика - исправленный формат
        $sleepData = Sleep::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        $sleepLabels = [];
        $sleepQuality = [];
        $sleepDuration = [];

        foreach ($sleepData as $record) {
            $sleepLabels[] = Carbon::parse($record->date)->format($groupFormat);
            $sleepQuality[] = $record->sleep_quality;
            $sleepDuration[] = round($record->sleep_duration / 60, 1);
        }

        // Данные активности для графика - исправленный формат
        $activityData = Activity::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        $activityLabels = [];
        $activitySteps = [];
        $activityCalories = [];

        foreach ($activityData as $record) {
            $activityLabels[] = Carbon::parse($record->date)->format($groupFormat);
            $activitySteps[] = $record->steps;
            $activityCalories[] = $record->calories_burned;
        }

        return response()->json([
            'sleep' => [
                'labels' => $sleepLabels,
                'quality' => $sleepQuality,
                'duration' => $sleepDuration
            ],
            'activity' => [
                'labels' => $activityLabels,
                'steps' => $activitySteps,
                'calories' => $activityCalories
            ]
        ]);
    }

    // ДОБАВЛЕННЫЕ МЕТОДЫ:

    public function getQuickStats()
    {
        $user = auth()->user();

        $todaySleep = Sleep::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $todayActivity = Activity::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $weeklySleepAvg = Sleep::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(7))
            ->avg('sleep_quality');

        $weeklyStepsAvg = Activity::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(7))
            ->avg('steps');

        return response()->json([
            'today_sleep_quality' => $todaySleep ? $todaySleep->sleep_quality : 0,
            'today_sleep_duration' => $todaySleep ? round($todaySleep->sleep_duration / 60, 1) : 0,
            'today_steps' => $todayActivity ? $todayActivity->steps : 0,
            'today_calories' => $todayActivity ? $todayActivity->calories_burned : 0,
            'weekly_sleep_avg' => round($weeklySleepAvg ?? 0),
            'weekly_steps_avg' => round($weeklyStepsAvg ?? 0)
        ]);
    }

    public function getRecentActivity()
    {
        $user = auth()->user();

        $recentSleep = Sleep::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get()
            ->map(function($sleep) {
                return [
                    'date' => $sleep->date->format('d.m.Y'),
                    'duration' => round($sleep->sleep_duration / 60, 1),
                    'quality' => $sleep->sleep_quality,
                    'time' => Carbon::parse($sleep->bedtime)->format('H:i') . ' - ' . Carbon::parse($sleep->wakeup_time)->format('H:i')
                ];
            });

        $recentActivity = Activity::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get()
            ->map(function($activity) {
                return [
                    'date' => $activity->date->format('d.m.Y'),
                    'steps' => number_format($activity->steps),
                    'calories' => $activity->calories_burned,
                    'active_minutes' => $activity->active_minutes
                ];
            });

        return response()->json([
            'recent_sleep' => $recentSleep,
            'recent_activity' => $recentActivity
        ]);
    }

    public function getHealthGoalsProgress()
    {
        $user = auth()->user();
        $healthData = UserHealthData::where('user_id', $user->id)->first();

        if (!$healthData) {
            return response()->json(['error' => 'Health data not found'], 404);
        }

        $todaySleep = Sleep::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $todayActivity = Activity::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $sleepProgress = $todaySleep ? min(100, ($todaySleep->sleep_duration / $healthData->sleep_goal) * 100) : 0;
        $stepsProgress = $todayActivity ? min(100, ($todayActivity->steps / $healthData->steps_goal) * 100) : 0;
        $caloriesProgress = $todayActivity ? min(100, ($todayActivity->calories_burned / $healthData->calories_goal) * 100) : 0;
        $activeMinutesProgress = $todayActivity ? min(100, ($todayActivity->active_minutes / $healthData->active_minutes_goal) * 100) : 0;

        return response()->json([
            'sleep' => [
                'current' => $todaySleep ? round($todaySleep->sleep_duration / 60, 1) : 0,
                'goal' => round($healthData->sleep_goal / 60, 1),
                'progress' => round($sleepProgress)
            ],
            'steps' => [
                'current' => $todayActivity ? $todayActivity->steps : 0,
                'goal' => $healthData->steps_goal,
                'progress' => round($stepsProgress)
            ],
            'calories' => [
                'current' => $todayActivity ? $todayActivity->calories_burned : 0,
                'goal' => $healthData->calories_goal,
                'progress' => round($caloriesProgress)
            ],
            'active_minutes' => [
                'current' => $todayActivity ? $todayActivity->active_minutes : 0,
                'goal' => $healthData->active_minutes_goal,
                'progress' => round($activeMinutesProgress)
            ]
        ]);
    }

    public function getWeeklySummary()
    {
        $user = auth()->user();

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $weeklySleep = Sleep::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $weeklyActivity = Activity::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $totalSleepHours = $weeklySleep->sum('sleep_duration') / 60;
        $avgSleepQuality = $weeklySleep->avg('sleep_quality');
        $totalSteps = $weeklyActivity->sum('steps');
        $totalCalories = $weeklyActivity->sum('calories_burned');
        $totalActiveMinutes = $weeklyActivity->sum('active_minutes');

        $healthData = UserHealthData::where('user_id', $user->id)->first();

        $sleepGoal = $healthData ? ($healthData->sleep_goal / 60) * 7 : 56; // 8 часов * 7 дней
        $stepsGoal = $healthData ? $healthData->steps_goal * 7 : 70000; // 10k * 7 дней
        $caloriesGoal = $healthData ? $healthData->calories_goal * 7 : 3500; // 500 * 7 дней
        $activeMinutesGoal = $healthData ? $healthData->active_minutes_goal * 7 : 210; // 30 * 7 дней

        return response()->json([
            'sleep' => [
                'total_hours' => round($totalSleepHours, 1),
                'avg_quality' => round($avgSleepQuality ?? 0),
                'goal' => $sleepGoal,
                'completion' => min(100, ($totalSleepHours / $sleepGoal) * 100)
            ],
            'activity' => [
                'total_steps' => $totalSteps,
                'total_calories' => $totalCalories,
                'total_active_minutes' => $totalActiveMinutes,
                'steps_goal' => $stepsGoal,
                'calories_goal' => $caloriesGoal,
                'active_minutes_goal' => $activeMinutesGoal,
                'steps_completion' => min(100, ($totalSteps / $stepsGoal) * 100),
                'calories_completion' => min(100, ($totalCalories / $caloriesGoal) * 100),
                'active_minutes_completion' => min(100, ($totalActiveMinutes / $activeMinutesGoal) * 100)
            ],
            'days_recorded' => [
                'sleep' => $weeklySleep->count(),
                'activity' => $weeklyActivity->count()
            ]
        ]);
    }
}
