<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->paginate(10);

        $activityStats = $this->getActivityStatistics(auth()->id());

        return view('activity.index', compact('activities', 'activityStats'));
    }

    public function create()
    {
        $today = now()->format('Y-m-d');
        $workoutTypes = [
            '' => 'Выберите тип активности',
            'walking' => 'Ходьба',
            'running' => 'Бег',
            'cycling' => 'Велосипед',
            'swimming' => 'Плавание',
            'yoga' => 'Йога',
            'gym' => 'Тренажерный зал',
            'dancing' => 'Танцы',
            'hiking' => 'Пеший туризм',
            'team_sports' => 'Командные виды спорта',
            'other' => 'Другое'
        ];

        return view('activity.create', compact('today', 'workoutTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'workout_type' => 'nullable|string|max:50',
            'steps' => 'required|integer|min:0',
            'calories_burned' => 'required|integer|min:0',
            'active_minutes' => 'required|integer|min:0',
            'distance' => 'required|numeric|min:0',
            'heart_rate' => 'nullable|integer|min:40|max:200',
            'notes' => 'nullable|string|max:500'
        ]);

        // Проверяем, не существует ли уже записи на эту дату
        $existingRecord = Activity::where('user_id', auth()->id())
            ->where('date', $validated['date'])
            ->first();

        if ($existingRecord) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Запись об активности на эту дату уже существует!');
        }

        Activity::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'workout_type' => $validated['workout_type'],
            'steps' => $validated['steps'],
            'calories_burned' => $validated['calories_burned'],
            'active_minutes' => $validated['active_minutes'],
            'distance' => $validated['distance'],
            'heart_rate' => $validated['heart_rate'],
            'notes' => $validated['notes']
        ]);

        return redirect()->route('activity.index')
            ->with('success', 'Активность успешно добавлена!');
    }

    public function show(Activity $activity)
    {
        $this->authorize('view', $activity);

        $activityScore = $activity->getActivityScore();
        $pace = $activity->pace;
        $analysis = $this->analyzeActivity($activity);

        return view('activity.show', compact('activity', 'activityScore', 'pace', 'analysis'));
    }

    public function edit(Activity $activity)
    {
        $this->authorize('update', $activity);

        $workoutTypes = [
            '' => 'Выберите тип активности',
            'walking' => 'Ходьба',
            'running' => 'Бег',
            'cycling' => 'Велосипед',
            'swimming' => 'Плавание',
            'yoga' => 'Йога',
            'gym' => 'Тренажерный зал',
            'dancing' => 'Танцы',
            'hiking' => 'Пеший туризм',
            'team_sports' => 'Командные виды спорта',
            'other' => 'Другое'
        ];

        return view('activity.edit', compact('activity', 'workoutTypes'));
    }

    public function update(Request $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $validated = $request->validate([
            'date' => 'required|date',
            'workout_type' => 'nullable|string|max:50',
            'steps' => 'required|integer|min:0',
            'calories_burned' => 'required|integer|min:0',
            'active_minutes' => 'required|integer|min:0',
            'distance' => 'required|numeric|min:0',
            'heart_rate' => 'nullable|integer|min:40|max:200',
            'notes' => 'nullable|string|max:500'
        ]);

        $activity->update($validated);

        return redirect()->route('activity.show', $activity)
            ->with('success', 'Активность успешно обновлена!');
    }

    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return redirect()->route('activity.index')
            ->with('success', 'Активность успешно удалена!');
    }

    private function analyzeActivity($activity)
    {
        $analysis = [];

        // Анализ шагов
        if ($activity->steps >= 10000) {
            $analysis[] = 'Отличное количество шагов!';
        } elseif ($activity->steps >= 7500) {
            $analysis[] = 'Хорошее количество шагов';
        } elseif ($activity->steps >= 5000) {
            $analysis[] = 'Умеренная активность';
        } else {
            $analysis[] = 'Низкая активность, рекомендуется больше двигаться';
        }

        // Анализ калорий
        if ($activity->calories_burned >= 500) {
            $analysis[] = 'Высокий расход калорий';
        } elseif ($activity->calories_burned >= 300) {
            $analysis[] = 'Хороший расход калорий';
        } else {
            $analysis[] = 'Умеренный расход калорий';
        }

        // Анализ активных минут
        if ($activity->active_minutes >= 60) {
            $analysis[] = 'Отличная продолжительность активности';
        } elseif ($activity->active_minutes >= 30) {
            $analysis[] = 'Рекомендуемая продолжительность активности достигнута';
        } else {
            $analysis[] = 'Рекомендуется увеличить продолжительность активности';
        }

        // Анализ пульса
        if ($activity->heart_rate) {
            if ($activity->heart_rate > 180) {
                $analysis[] = 'Очень высокая частота пульса';
            } elseif ($activity->heart_rate > 150) {
                $analysis[] = 'Высокая интенсивность тренировки';
            } elseif ($activity->heart_rate > 120) {
                $analysis[] = 'Умеренная интенсивность тренировки';
            } else {
                $analysis[] = 'Низкая интенсивность тренировки';
            }
        }

        return $analysis;
    }

    private function getActivityStatistics($userId)
    {
        $stats = Activity::where('user_id', $userId)
            ->select(
                DB::raw('AVG(steps) as avg_steps'),
                DB::raw('AVG(calories_burned) as avg_calories'),
                DB::raw('AVG(active_minutes) as avg_minutes'),
                DB::raw('COUNT(*) as total_records'),
                DB::raw('SUM(distance) as total_distance'),
                DB::raw('MAX(steps) as max_steps')
            )
            ->first();

        $workoutDistribution = Activity::where('user_id', $userId)
            ->whereNotNull('workout_type')
            ->groupBy('workout_type')
            ->select('workout_type', DB::raw('COUNT(*) as count'))
            ->get()
            ->pluck('count', 'workout_type');

        return [
            'avg_steps' => round($stats->avg_steps),
            'avg_calories' => round($stats->avg_calories),
            'avg_minutes' => round($stats->avg_minutes),
            'total_records' => $stats->total_records,
            'total_distance' => round($stats->total_distance, 1),
            'max_steps' => $stats->max_steps,
            'workout_distribution' => $workoutDistribution
        ];
    }

    public function getWeeklySummary()
    {
        $user = auth()->user();
        $startDate = now()->subDays(6)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $weeklyData = Activity::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(steps) as total_steps'),
                DB::raw('SUM(calories_burned) as total_calories'),
                DB::raw('SUM(active_minutes) as total_minutes'),
                DB::raw('SUM(distance) as total_distance'),
                DB::raw('COUNT(*) as active_days')
            )
            ->first();

        $goalAchievement = [
            'steps' => $weeklyData->total_steps >= 70000 ? 100 : round(($weeklyData->total_steps / 70000) * 100),
            'calories' => $weeklyData->total_calories >= 3500 ? 100 : round(($weeklyData->total_calories / 3500) * 100),
            'minutes' => $weeklyData->total_minutes >= 210 ? 100 : round(($weeklyData->total_minutes / 210) * 100),
            'days' => round(($weeklyData->active_days / 7) * 100)
        ];

        return response()->json([
            'weekly_data' => $weeklyData,
            'goal_achievement' => $goalAchievement
        ]);
    }
}
