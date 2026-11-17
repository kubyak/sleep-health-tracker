<?php

namespace App\Http\Controllers;

use App\Models\FitnessDevice;
use App\Services\GoogleFitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FitnessDeviceController extends Controller
{
    protected $googleFitService;

    public function __construct(GoogleFitService $googleFitService)
    {
        $this->googleFitService = $googleFitService;
    }

    /**
     * Страница управления фитнес-устройствами
     */
    public function index()
    {
        $devices = FitnessDevice::where('user_id', Auth::id())->get();

        return view('fitness.devices', compact('devices'));
    }

    /**
     * Подключение Google Fit
     */
    public function connectGoogleFit()
    {
        try {
            $authUrl = $this->googleFitService->getAuthUrl();
            return redirect()->away($authUrl);

        } catch (\Exception $e) {
            Log::error('Google Fit Connect Error: ' . $e->getMessage());
            return redirect()->route('fitness.devices')
                ->with('error', 'Ошибка при подключении Google Fit: ' . $e->getMessage());
        }
    }

    /**
     * Callback от Google Fit OAuth
     */
    public function googleFitCallback(Request $request)
    {
        try {
            // Проверяем state параметр для безопасности
            $state = $request->get('state');
            if (!$this->googleFitService->validateState($state)) {
                return redirect()->route('fitness.devices')
                    ->with('error', 'Неверный state параметр. Попробуйте снова.');
            }

            // Проверяем наличие кода авторизации
            $code = $request->get('code');
            if (!$code) {
                $error = $request->get('error');
                $errorDescription = $request->get('error_description');

                Log::error('Google Fit OAuth Error: ' . $error . ' - ' . $errorDescription);
                return redirect()->route('fitness.devices')
                    ->with('error', 'Ошибка авторизации: ' . ($errorDescription ?: $error));
            }

            // Получаем access token
            $tokens = $this->googleFitService->getAccessToken($code);
            if (!$tokens) {
                return redirect()->route('fitness.devices')
                    ->with('error', 'Не удалось получить доступ к Google Fit');
            }

            // Проверяем валидность токена
            if (!$this->googleFitService->validateAccessToken($tokens['access_token'])) {
                return redirect()->route('fitness.devices')
                    ->with('error', 'Невалидный access token');
            }

            // Получаем информацию о пользователе
            $userInfo = $this->googleFitService->getUserInfo($tokens['access_token']);

            // Сохраняем/обновляем подключение
            $device = FitnessDevice::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'api_provider' => 'google_fit'
                ],
                [
                    'device_type' => 'google_fit',
                    'device_name' => $userInfo['name'] ?? 'Google Fit Account',
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'] ?? null,
                    'token_expires_at' => Carbon::createFromTimestamp($tokens['expires_at']),
                    'is_connected' => true,
                    'last_sync_at' => null,
                    'sync_settings' => [
                        'sync_sleep' => true,
                        'sync_activity' => true,
                        'sync_heart_rate' => true,
                        'auto_sync' => true,
                        'sync_interval' => 60
                    ],
                    'raw_data' => [
                        'user_info' => $userInfo,
                        'connected_at' => now()->toISOString()
                    ]
                ]
            );

            // Пробуем выполнить первую синхронизацию
            $syncResult = $this->syncGoogleFitData($device);

            return redirect()->route('fitness.devices')
                ->with('success', 'Google Fit успешно подключен! ' .
                    ($syncResult ? 'Первая синхронизация выполнена.' : 'Синхронизация не удалась.'));

        } catch (\Exception $e) {
            Log::error('Google Fit Callback Error: ' . $e->getMessage());
            return redirect()->route('fitness.devices')
                ->with('error', 'Ошибка при подключении Google Fit: ' . $e->getMessage());
        }
    }

    /**
     * Синхронизация данных Google Fit
     */
    public function syncGoogleFit($deviceId)
    {
        try {
            $device = FitnessDevice::where('user_id', Auth::id())
                ->where('id', $deviceId)
                ->where('api_provider', 'google_fit')
                ->firstOrFail();

            if (!$device->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Устройство не активно или требуется обновление токена'
                ], 400);
            }

            $result = $this->syncGoogleFitData($device);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Данные успешно синхронизированы',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось синхронизировать данные'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Google Fit Sync Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка синхронизации: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновление токена Google Fit
     */
    public function refreshGoogleFitToken($deviceId)
    {
        try {
            $device = FitnessDevice::where('user_id', Auth::id())
                ->where('id', $deviceId)
                ->where('api_provider', 'google_fit')
                ->firstOrFail();

            if (!$device->refresh_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refresh token отсутствует'
                ], 400);
            }

            $newTokens = $this->googleFitService->refreshAccessToken($device->refresh_token);

            if ($newTokens) {
                $device->update([
                    'access_token' => $newTokens['access_token'],
                    'token_expires_at' => Carbon::createFromTimestamp($newTokens['expires_at']),
                    'last_sync_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Токен успешно обновлен'
                ]);
            } else {
                // Если refresh token не работает, отключаем устройство
                $device->update([
                    'is_connected' => false,
                    'access_token' => null,
                    'refresh_token' => null
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось обновить токен. Устройство отключено.'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Google Fit Token Refresh Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка обновления токена: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отключение Google Fit
     */
    public function disconnectGoogleFit($deviceId)
    {
        try {
            $device = FitnessDevice::where('user_id', Auth::id())
                ->where('id', $deviceId)
                ->where('api_provider', 'google_fit')
                ->firstOrFail();

            $device->update([
                'is_connected' => false,
                'access_token' => null,
                'refresh_token' => null,
                'token_expires_at' => null
            ]);

            return redirect()->route('fitness.devices')
                ->with('success', 'Google Fit успешно отключен');

        } catch (\Exception $e) {
            Log::error('Google Fit Disconnect Error: ' . $e->getMessage());
            return redirect()->route('fitness.devices')
                ->with('error', 'Ошибка при отключении Google Fit');
        }
    }

    /**
     * Внутренний метод синхронизации данных Google Fit
     */
    private function syncGoogleFitData(FitnessDevice $device): array
    {
        $results = [
            'sleep' => false,
            'activity' => false,
            'heart_rate' => false
        ];

        try {
            // Устанавливаем период синхронизации (последние 24 часа)
            $endTime = now()->getTimestamp() * 1000;
            $startTime = now()->subDay()->getTimestamp() * 1000;

            $settings = $device->sync_settings;

            // Синхронизация данных о сне
            if ($settings['sync_sleep'] ?? true) {
                $sleepData = $this->googleFitService->getSleepData(
                    $device->access_token,
                    $startTime,
                    $endTime
                );

                if ($sleepData && $sleepData['total_sleep_minutes'] > 0) {
                    $this->saveSleepData($sleepData);
                    $results['sleep'] = true;
                    Log::info('Google Fit Sleep Data Synced', [
                        'user_id' => Auth::id(),
                        'sleep_minutes' => $sleepData['total_sleep_minutes']
                    ]);
                }
            }

            // Синхронизация данных об активности
            if ($settings['sync_activity'] ?? true) {
                $activityData = $this->googleFitService->getActivityData(
                    $device->access_token,
                    $startTime,
                    $endTime
                );

                if ($activityData) {
                    $this->saveActivityData($activityData);
                    $results['activity'] = true;
                    Log::info('Google Fit Activity Data Synced', [
                        'user_id' => Auth::id(),
                        'steps' => $activityData['steps']
                    ]);
                }
            }

            // Синхронизация данных о сердечном ритме
            if ($settings['sync_heart_rate'] ?? true) {
                $heartRateData = $this->googleFitService->getHeartRateData(
                    $device->access_token,
                    $startTime,
                    $endTime
                );

                if ($heartRateData && !empty($heartRateData['readings'])) {
                    $this->saveHeartRateData($heartRateData);
                    $results['heart_rate'] = true;
                    Log::info('Google Fit Heart Rate Data Synced', [
                        'user_id' => Auth::id(),
                        'readings' => count($heartRateData['readings'])
                    ]);
                }
            }

            // Обновляем время последней синхронизации
            $device->update(['last_sync_at' => now()]);

            return $results;

        } catch (\Exception $e) {
            Log::error('Google Fit Data Sync Error: ' . $e->getMessage());
            return $results;
        }
    }

    /**
     * Сохранение данных о сне в базу
     */
    private function saveSleepData(array $sleepData): void
    {
        $sleepDate = now()->format('Y-m-d');

        // Если данные за сегодня уже есть, обновляем их
        $existingSleep = \App\Models\Sleep::where('user_id', Auth::id())
            ->where('date', $sleepDate)
            ->first();

        if ($existingSleep) {
            // Обновляем существующую запись
            $existingSleep->update([
                'bedtime' => $sleepData['bedtime'] ?? $existingSleep->bedtime,
                'wakeup_time' => $sleepData['wakeup_time'] ?? $existingSleep->wakeup_time,
                'sleep_duration' => $sleepData['total_sleep_minutes'],
                'deep_sleep' => $sleepData['deep_sleep_minutes'],
                'light_sleep' => $sleepData['light_sleep_minutes'],
                'rem_sleep' => $sleepData['rem_sleep_minutes'],
                'awake_time' => $sleepData['awake_minutes'],
                'sleep_quality' => $this->calculateSleepQuality($sleepData),
                'notes' => 'Автоматическая синхронизация с Google Fit'
            ]);
        } else {
            // Создаем новую запись
            \App\Models\Sleep::create([
                'user_id' => Auth::id(),
                'date' => $sleepDate,
                'bedtime' => $sleepData['bedtime'] ?? '22:00',
                'wakeup_time' => $sleepData['wakeup_time'] ?? '07:00',
                'sleep_duration' => $sleepData['total_sleep_minutes'],
                'deep_sleep' => $sleepData['deep_sleep_minutes'],
                'light_sleep' => $sleepData['light_sleep_minutes'],
                'rem_sleep' => $sleepData['rem_sleep_minutes'],
                'awake_time' => $sleepData['awake_minutes'],
                'sleep_quality' => $this->calculateSleepQuality($sleepData),
                'notes' => 'Автоматическая синхронизация с Google Fit'
            ]);
        }
    }

    /**
     * Сохранение данных об активности
     */
    private function saveActivityData(array $activityData): void
    {
        $activityDate = now()->format('Y-m-d');

        $existingActivity = \App\Models\Activity::where('user_id', Auth::id())
            ->where('date', $activityDate)
            ->first();

        if ($existingActivity) {
            $existingActivity->update([
                'steps' => $activityData['steps'],
                'calories_burned' => $activityData['calories'],
                'active_minutes' => $activityData['active_minutes'],
                'distance' => $activityData['distance_km'],
                'workout_type' => 'auto_sync',
                'heart_rate' => $existingActivity->heart_rate, // Сохраняем существующий пульс
                'notes' => 'Автоматическая синхронизация с Google Fit'
            ]);
        } else {
            \App\Models\Activity::create([
                'user_id' => Auth::id(),
                'date' => $activityDate,
                'steps' => $activityData['steps'],
                'calories_burned' => $activityData['calories'],
                'active_minutes' => $activityData['active_minutes'],
                'distance' => $activityData['distance_km'],
                'workout_type' => 'auto_sync',
                'notes' => 'Автоматическая синхронизация с Google Fit'
            ]);
        }
    }

    /**
     * Сохранение данных о сердечном ритме
     */
    private function saveHeartRateData(array $heartRateData): void
    {
        // Обновляем пульс в последней записи активности
        $todayActivity = \App\Models\Activity::where('user_id', Auth::id())
            ->where('date', now()->format('Y-m-d'))
            ->first();

        if ($todayActivity) {
            $todayActivity->update([
                'heart_rate' => $heartRateData['average_bpm'],
                'notes' => $todayActivity->notes . ' | Пульс: ' . $heartRateData['average_bpm'] . ' уд/мин'
            ]);
        }

        // Можно также сохранить детальные данные о пульсе в отдельную таблицу
        // Для простоты пока сохраняем только среднее значение
    }

    /**
     * Расчет качества сна на основе данных Google Fit
     */
    private function calculateSleepQuality(array $sleepData): int
    {
        $totalSleep = $sleepData['total_sleep_minutes'];
        $deepSleep = $sleepData['deep_sleep_minutes'];
        $awakeTime = $sleepData['awake_minutes'];
        $efficiency = $sleepData['sleep_efficiency'];

        if ($totalSleep === 0) {
            return 0;
        }

        // Баллы за эффективность сна (0-40 баллов)
        $efficiencyScore = min(40, ($efficiency / 100) * 40);

        // Баллы за глубокий сон (0-30 баллов)
        $deepSleepRatio = ($deepSleep / $totalSleep) * 100;
        $deepSleepScore = min(30, ($deepSleepRatio / 25) * 30);

        // Баллы за общую продолжительность (0-30 баллов)
        $durationScore = 0;
        if ($totalSleep >= 420 && $totalSleep <= 540) { // 7-9 часов - идеально
            $durationScore = 30;
        } elseif ($totalSleep >= 360 && $totalSleep < 420) { // 6-7 часов - хорошо
            $durationScore = 20;
        } elseif ($totalSleep >= 300 && $totalSleep < 360) { // 5-6 часов - удовлетворительно
            $durationScore = 15;
        } elseif ($totalSleep >= 240 && $totalSleep < 300) { // 4-5 часов - плохо
            $durationScore = 10;
        } else { // Меньше 4 часов - очень плохо
            $durationScore = 5;
        }

        $totalScore = $efficiencyScore + $deepSleepScore + $durationScore;

        return min(100, max(0, (int) $totalScore));
    }

    /**
     * Обновление настроек синхронизации
     */
    public function updateSyncSettings(Request $request, $deviceId)
    {
        try {
            $device = FitnessDevice::where('user_id', Auth::id())
                ->where('id', $deviceId)
                ->firstOrFail();

            $settings = $device->sync_settings;
            $newSettings = array_merge($settings, $request->only([
                'sync_sleep', 'sync_activity', 'sync_heart_rate', 'auto_sync', 'sync_interval'
            ]));

            $device->update([
                'sync_settings' => $newSettings
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Настройки синхронизации обновлены'
            ]);

        } catch (\Exception $e) {
            Log::error('Sync Settings Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка обновления настроек'
            ], 500);
        }
    }
}
