<?php

namespace App\Http\Controllers;

use App\Models\Sleep;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SleepController extends Controller
{
    public function index()
    {
        $sleepRecords = Sleep::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->paginate(10);

        $sleepStats = $this->getSleepStatistics(auth()->id());

        return view('sleep.index', compact('sleepRecords', 'sleepStats'));
    }

    public function create()
    {
        $today = now()->format('Y-m-d');
        $defaultBedtime = '22:00';
        $defaultWakeup = '06:00';

        return view('sleep.create', compact('today', 'defaultBedtime', 'defaultWakeup'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'bedtime' => 'required',
            'wakeup_time' => 'required',
            'deep_sleep' => 'nullable|integer|min:0',
            'light_sleep' => 'nullable|integer|min:0',
            'rem_sleep' => 'nullable|integer|min:0',
            'awake_time' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        // Проверяем, не существует ли уже записи на эту дату
        $existingRecord = Sleep::where('user_id', auth()->id())
            ->where('date', $validated['date'])
            ->first();

        if ($existingRecord) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Запись о сне на эту дату уже существует!');
        }

        // Рассчитываем продолжительность сна
        $bedtime = Carbon::parse($validated['bedtime']);
        $wakeup = Carbon::parse($validated['wakeup_time']);

        if ($wakeup->lt($bedtime)) {
            $wakeup->addDay();
        }

        $sleepDuration = $bedtime->diffInMinutes($wakeup);

        // Автоматически распределяем фазы сна, если не указаны
        $phases = $this->calculateSleepPhases($sleepDuration, $validated);

        $sleep = Sleep::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'bedtime' => $validated['bedtime'],
            'wakeup_time' => $validated['wakeup_time'],
            'sleep_duration' => $sleepDuration,
            'deep_sleep' => $phases['deep_sleep'],
            'light_sleep' => $phases['light_sleep'],
            'rem_sleep' => $phases['rem_sleep'],
            'awake_time' => $phases['awake_time'],
            'notes' => $validated['notes']
        ]);

        // Рассчитываем и сохраняем качество сна
        $sleep->sleep_quality = $sleep->calculateSleepQuality();
        $sleep->save();

        return redirect()->route('sleep.index')
            ->with('success', 'Запись о сне успешно добавлена! Качество сна: ' . $sleep->sleep_quality . '%');
    }

    public function show(Sleep $sleep)
    {
        $this->authorize('view', $sleep);

        $sleepPhases = $sleep->sleep_phases;
        $sleepEfficiency = $sleep->sleep_efficiency;

        // Анализ качества сна
        $sleepAnalysis = $this->analyzeSleepQuality($sleep);

        return view('sleep.show', compact('sleep', 'sleepPhases', 'sleepEfficiency', 'sleepAnalysis'));
    }

    public function edit(Sleep $sleep)
    {
        $this->authorize('update', $sleep);

        return view('sleep.edit', compact('sleep'));
    }

    public function update(Request $request, Sleep $sleep)
    {
        $this->authorize('update', $sleep);

        $validated = $request->validate([
            'date' => 'required|date',
            'bedtime' => 'required',
            'wakeup_time' => 'required',
            'deep_sleep' => 'nullable|integer|min:0',
            'light_sleep' => 'nullable|integer|min:0',
            'rem_sleep' => 'nullable|integer|min:0',
            'awake_time' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        // Пересчитываем продолжительность сна
        $bedtime = Carbon::parse($validated['bedtime']);
        $wakeup = Carbon::parse($validated['wakeup_time']);

        if ($wakeup->lt($bedtime)) {
            $wakeup->addDay();
        }

        $sleepDuration = $bedtime->diffInMinutes($wakeup);

        $sleep->update([
            'date' => $validated['date'],
            'bedtime' => $validated['bedtime'],
            'wakeup_time' => $validated['wakeup_time'],
            'sleep_duration' => $sleepDuration,
            'deep_sleep' => $validated['deep_sleep'],
            'light_sleep' => $validated['light_sleep'],
            'rem_sleep' => $validated['rem_sleep'],
            'awake_time' => $validated['awake_time'],
            'notes' => $validated['notes']
        ]);

        // Пересчитываем качество сна
        $sleep->sleep_quality = $sleep->calculateSleepQuality();
        $sleep->save();

        return redirect()->route('sleep.show', $sleep)
            ->with('success', 'Запись о сне успешно обновлена!');
    }

    public function destroy(Sleep $sleep)
    {
        $this->authorize('delete', $sleep);

        $sleep->delete();

        return redirect()->route('sleep.index')
            ->with('success', 'Запись о сне успешно удалена!');
    }

    private function calculateSleepPhases($totalDuration, $data)
    {
        // Если пользователь указал все фазы, используем их
        if ($data['deep_sleep'] && $data['light_sleep'] && $data['rem_sleep'] && $data['awake_time']) {
            return [
                'deep_sleep' => $data['deep_sleep'],
                'light_sleep' => $data['light_sleep'],
                'rem_sleep' => $data['rem_sleep'],
                'awake_time' => $data['awake_time']
            ];
        }

        // Автоматическое распределение фаз сна на основе общей продолжительности
        $awakeTime = $data['awake_time'] ?? rand(5, 20); // 5-20 минут бодрствования
        $remainingTime = $totalDuration - $awakeTime;

        // Типичное распределение фаз сна для взрослого человека
        $deepSleep = $data['deep_sleep'] ?? round($remainingTime * 0.15); // 15% глубокий сон
        $remSleep = $data['rem_sleep'] ?? round($remainingTime * 0.25);   // 25% REM сон
        $lightSleep = $data['light_sleep'] ?? $remainingTime - $deepSleep - $remSleep; // Остальное - легкий сон

        return [
            'deep_sleep' => $deepSleep,
            'light_sleep' => $lightSleep,
            'rem_sleep' => $remSleep,
            'awake_time' => $awakeTime
        ];
    }

    private function analyzeSleepQuality($sleep)
    {
        $analysis = [];

        // Анализ продолжительности
        $durationHours = $sleep->sleep_duration / 60;
        if ($durationHours >= 7 && $durationHours <= 9) {
            $analysis[] = 'Оптимальная продолжительность сна';
        } elseif ($durationHours < 7) {
            $analysis[] = 'Недостаточная продолжительность сна';
        } else {
            $analysis[] = 'Избыточная продолжительность сна';
        }

        // Анализ качества
        if ($sleep->sleep_quality >= 80) {
            $analysis[] = 'Отличное качество сна';
        } elseif ($sleep->sleep_quality >= 60) {
            $analysis[] = 'Хорошее качество сна';
        } else {
            $analysis[] = 'Низкое качество сна';
        }

        // Анализ эффективности
        if ($sleep->sleep_efficiency >= 90) {
            $analysis[] = 'Высокая эффективность сна';
        } elseif ($sleep->sleep_efficiency >= 85) {
            $analysis[] = 'Хорошая эффективность сна';
        } else {
            $analysis[] = 'Низкая эффективность сна';
        }

        return $analysis;
    }

    private function getSleepStatistics($userId)
    {
        $stats = Sleep::where('user_id', $userId)
            ->select(
                DB::raw('AVG(sleep_duration) as avg_duration'),
                DB::raw('AVG(sleep_quality) as avg_quality'),
                DB::raw('COUNT(*) as total_records'),
                DB::raw('MAX(sleep_quality) as best_quality'),
                DB::raw('MIN(sleep_quality) as worst_quality')
            )
            ->first();

        $recentTrend = Sleep::where('user_id', $userId)
            ->where('date', '>=', now()->subDays(7))
            ->avg('sleep_quality');

        return [
            'avg_duration' => round($stats->avg_duration / 60, 1),
            'avg_quality' => round($stats->avg_quality),
            'total_records' => $stats->total_records,
            'best_quality' => $stats->best_quality,
            'worst_quality' => $stats->worst_quality,
            'recent_trend' => round($recentTrend)
        ];
    }
}
