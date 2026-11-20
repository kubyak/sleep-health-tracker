<?php

namespace App\Http\Controllers;

use App\Models\Sleep;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SleepController extends Controller
{
    /**
     * Показать список записей о сне текущего пользователя.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index()
    {
        $sleepRecords = Sleep::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->paginate(10);

        $sleepStats = $this->getSleepStatistics(Auth::id());

        return view('sleep.index', compact('sleepRecords', 'sleepStats'));
    }

    /**
     * Форма для создания новой записи о сне.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create()
    {
        $today = now()->format('Y-m-d');
        $defaultBedtime = '22:00';
        $defaultWakeup = '06:00';

        // Проверка, есть ли уже запись на сегодняшний день
        $existingToday = Sleep::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        if ($existingToday) {
            return redirect()->route('sleep.edit', $existingToday)
                ->with('info', 'Запись о сне на сегодня уже существует. Вы можете отредактировать её.');
        }

        return view('sleep.create', compact('today', 'defaultBedtime', 'defaultWakeup'));
    }

    /**
     * Создание новой записи о сне.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'date' => 'required|date',
                'bedtime' => 'required',
                'wakeup_time' => 'required',
                'deep_sleep' => 'nullable|integer|min:0',
                'light_sleep' => 'nullable|integer|min:0',
                'rem_sleep' => 'nullable|integer|min:0',
                'awake_time' => 'nullable|integer|min:0',
                'notes' => 'nullable|string|max:500'
            ]);

            // Проверка существования записи на выбранную дату
            $existingRecord = Sleep::where('user_id', Auth::id())
                ->where('date', $validatedData['date'])
                ->first();

            if ($existingRecord) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Запись о сне на указанную дату уже существует!');
            }

            // Разбор временных значений
            $bedtime = Carbon::parse($validatedData['date'] . ' ' . $validatedData['bedtime']);
            $wakeup = Carbon::parse($validatedData['date'] . ' ' . $validatedData['wakeup_time']);

            // Переход через полночь
            if ($wakeup->lt($bedtime)) {
                $wakeup->addDay();
            }

            // Время сна в минутах
            $sleepDuration = $bedtime->diffInMinutes($wakeup);

            // Минимально-допустимая продолжительность сна
            if ($sleepDuration < 180 || $sleepDuration > 720) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Продолжительность сна должна составлять от 3 до 12 часов.');
            }

            // Определение фаз сна (если они отсутствуют)
            $phases = $this->calculateSleepPhases($sleepDuration, $validatedData);

            // Создание записи
            $sleep = Sleep::create([
                'user_id' => Auth::id(),
                'date' => $validatedData['date'],
                'bedtime' => $validatedData['bedtime'],
                'wakeup_time' => $validatedData['wakeup_time'],
                'sleep_duration' => $sleepDuration,
                'deep_sleep' => $phases['deep_sleep'],
                'light_sleep' => $phases['light_sleep'],
                'rem_sleep' => $phases['rem_sleep'],
                'awake_time' => $phases['awake_time'],
                'notes' => $validatedData['notes']
            ]);

            // Расчёт и сохранение качества сна
            $sleep->sleep_quality = $sleep->calculateSleepQuality();
            $sleep->save();

            return redirect()->route('sleep.index')
                ->with('success', 'Запись о сне успешно добавлена! Качество сна: ' . $sleep->sleep_quality . '%');

        } catch (\Throwable $th) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Произошла ошибка при создании записи о сне.');
        }
    }

    /**
     * Просмотр конкретной записи о сне.
     *
     * @param Sleep $sleep
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function show(Sleep $sleep)
    {
        // Авторизация (на случай необходимости защиты маршрута)
        //$this->authorize('view', $sleep);

        $sleepPhases = $sleep->sleep_phases;
        $sleepEfficiency = $sleep->sleep_efficiency;
        $sleepAnalysis = $this->analyzeSleepQuality($sleep);

        return view('sleep.show', compact('sleep', 'sleepPhases', 'sleepEfficiency', 'sleepAnalysis'));
    }

    /**
     * Редактирование записи о сне.
     *
     * @param Sleep $sleep
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(Sleep $sleep)
    {
        // Авторизация (на случай необходимости защиты маршрута)
        //$this->authorize('update', $sleep);

        return view('sleep.edit', compact('sleep'));
    }

    /**
     * Обновление записи о сне.
     *
     * @param Request $request
     * @param Sleep $sleep
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Sleep $sleep)
    {
        // Авторизация (на случай необходимости защиты маршрута)
        //$this->authorize('update', $sleep);

        $validatedData = $request->validate([
            'date' => 'required|date',
            'bedtime' => 'required',
            'wakeup_time' => 'required',
            'sleep_quality' => 'nullable|integer|min:0|max:100',
            'deep_sleep' => 'nullable|integer|min:0',
            'light_sleep' => 'nullable|integer|min:0',
            'rem_sleep' => 'nullable|integer|min:0',
            'awake_time' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        // Проверка на существование другого сна на заданную дату (кроме самой себя)
        $existingRecord = Sleep::where('user_id', Auth::id())
            ->where('date', $validatedData['date'])
            ->where('id', '!=', $sleep->id)
            ->first();

        if ($existingRecord) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Запись о сне на данную дату уже существует!');
        }

        // Разбор временного формата
        $bedtime = Carbon::parse($validatedData['date'] . ' ' . $validatedData['bedtime']);
        $wakeup = Carbon::parse($validatedData['date'] . ' ' . $validatedData['wakeup_time']);

        // Переход через полночь
        if ($wakeup->lt($bedtime)) {
            $wakeup->addDay();
        }

        // Подсчет продолжительности сна
        $sleepDuration = $bedtime->diffInMinutes($wakeup);

        // Ограничения по продолжительности сна
        if ($sleepDuration < 180 || $sleepDuration > 720) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Продолжительность сна должна составлять от 3 до 12 часов.');
        }

        // Актуализация записи
        $sleep->update([
            'date' => $validatedData['date'],
            'bedtime' => $validatedData['bedtime'],
            'wakeup_time' => $validatedData['wakeup_time'],
            'sleep_duration' => $sleepDuration,
            'sleep_quality' => $validatedData['sleep_quality'] ?? $sleep->calculateSleepQuality(),
            'deep_sleep' => $validatedData['deep_sleep'],
            'light_sleep' => $validatedData['light_sleep'],
            'rem_sleep' => $validatedData['rem_sleep'],
            'awake_time' => $validatedData['awake_time'],
            'notes' => $validatedData['notes']
        ]);

        return redirect()->route('sleep.show', $sleep)
            ->with('success', 'Запись о сне успешно обновлена!');
    }

    /**
     * Удаление записи о сне.
     *
     * @param Sleep $sleep
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Sleep $sleep)
    {
        // Авторизация (на случай необходимости защиты маршрута)
        //$this->authorize('delete', $sleep);

        $sleep->delete();

        return redirect()->route('sleep.index')
            ->with('success', 'Запись о сне успешно удалена!');
    }

    /**
     * Расчёт статистики сна.
     *
     * @param int $userId
     * @return array
     */
    private function getSleepStatistics($userId)
    {
        $stats = Sleep::where('user_id', $userId)
            ->selectRaw('AVG(sleep_duration) AS avg_duration, AVG(sleep_quality) AS avg_quality, COUNT(*) AS total_records, MAX(sleep_quality) AS best_quality, MIN(sleep_quality) AS worst_quality')
            ->first();

        return [
            'avg_duration' => round($stats->avg_duration / 60, 1),
            'avg_quality' => round($stats->avg_quality),
            'total_records' => $stats->total_records,
            'best_quality' => $stats->best_quality,
            'worst_quality' => $stats->worst_quality
        ];
    }

    /**
     * Анализ качества сна для конкретной записи.
     *
     * @param Sleep $sleep
     * @return array
     */
    private function analyzeSleepQuality(Sleep $sleep)
    {
        $analysis = [];

        // Анализируем продолжительность сна
        $durationHours = $sleep->sleep_duration / 60;
        if ($durationHours >= 7 && $durationHours <= 9) {
            $analysis[] = 'Оптимальная продолжительность сна';
        } elseif ($durationHours < 7) {
            $analysis[] = 'Недостаточная продолжительность сна';
        } else {
            $analysis[] = 'Избыточная продолжительность сна';
        }

        // Анализируем качество сна
        if ($sleep->sleep_quality >= 80) {
            $analysis[] = 'Отличное качество сна';
        } elseif ($sleep->sleep_quality >= 60) {
            $analysis[] = 'Хорошее качество сна';
        } else {
            $analysis[] = 'Низкое качество сна';
        }

        // Дополнительно можем анализировать глубокое и лёгкое состояние сна
        if ($sleep->deep_sleep_percentage < 15) {
            $analysis[] = 'Необходимо увеличить количество глубокого сна';
        }

        return $analysis;
    }
   

    /**
     * Автоопределение фаз сна (если пользователь не ввёл точные данные).
     *
     * @param int $totalDuration общее время сна в минутах
     * @param array $data массив данных формы
     * @return array
     */
    private function calculateSleepPhases($totalDuration, $data)
    {
        // Проверяем, введены ли данные пользователем
        if (
            isset($data['deep_sleep'], $data['light_sleep'], $data['rem_sleep'], $data['awake_time']) &&
            !empty($data['deep_sleep']) && !empty($data['light_sleep']) && !empty($data['rem_sleep']) && !empty($data['awake_time'])
        ) {
            return [
                'deep_sleep' => $data['deep_sleep'],
                'light_sleep' => $data['light_sleep'],
                'rem_sleep' => $data['rem_sleep'],
                'awake_time' => $data['awake_time']
            ];
        }

        // Генерируем автофазу сна
        $awakeTime = $data['awake_time'] ?? rand(5, 20);
        $remainingTime = $totalDuration - $awakeTime;

        // Оптимальное распределение фаз сна согласно рекомендациям
        $deepSleep = round($remainingTime * 0.15);
        $remSleep = round($remainingTime * 0.25);
        $lightSleep = $remainingTime - $deepSleep - $remSleep;

        return [
            'deep_sleep' => max($deepSleep, 0),
            'light_sleep' => max($lightSleep, 0),
            'rem_sleep' => max($remSleep, 0),
            'awake_time' => min($awakeTime, $totalDuration)
        ];
    }
}