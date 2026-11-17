<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GoogleFitService
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scopes;

    public function __construct()
    {
        $this->clientId = config('services.google_fit.client_id');
        $this->clientSecret = config('services.google_fit.client_secret');
        $this->redirectUri = config('services.google_fit.redirect');
        $this->scopes = config('services.google_fit.scopes', []);
    }

    /**
     * Генерация URL для авторизации
     */
    public function getAuthUrl(): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $this->scopes),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $this->generateState(),
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Генерация state параметра для безопасности
     */
    private function generateState(): string
    {
        $state = bin2hex(random_bytes(16));
        Cache::put('google_fit_state_' . $state, true, 600); // 10 minutes

        return $state;
    }

    /**
     * Проверка state параметра
     */
    public function validateState($state): bool
    {
        return Cache::pull('google_fit_state_' . $state) === true;
    }

    /**
     * Обмен authorization code на access token
     */
    public function getAccessToken($code): ?array
    {
        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Добавляем время истечения токена
                $data['expires_at'] = now()->addSeconds($data['expires_in'])->timestamp;

                return $data;
            }

            Log::error('Google Fit Token Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Google Fit Token Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Обновление access token
     */
    public function refreshAccessToken($refreshToken): ?array
    {
        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $data['expires_at'] = now()->addSeconds($data['expires_in'])->timestamp;

                return $data;
            }

            Log::error('Google Fit Token Refresh Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Google Fit Token Refresh Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение данных о сне за период
     */
    public function getSleepData($accessToken, $startTime, $endTime): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post('https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate', [
                    "aggregateBy" => [
                        [
                            "dataTypeName" => "com.google.sleep.segment"
                        ]
                    ],
                    "endTimeMillis" => $endTime,
                    "startTimeMillis" => $startTime
                ]);

            if ($response->successful()) {
                return $this->parseSleepData($response->json(), $startTime, $endTime);
            }

            Log::error('Google Fit Sleep API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Google Fit Sleep API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение данных об активности
     */
    public function getActivityData($accessToken, $startTime, $endTime): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post('https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate', [
                    "aggregateBy" => [
                        [
                            "dataTypeName" => "com.google.step_count.delta",
                            "dataSourceId" => "derived:com.google.step_count.delta:com.google.android.gms:estimated_steps"
                        ],
                        [
                            "dataTypeName" => "com.google.calories.expended",
                            "dataSourceId" => "derived:com.google.calories.expended:com.google.android.gms:merge_calories_expended"
                        ],
                        [
                            "dataTypeName" => "com.google.active_minutes",
                            "dataSourceId" => "derived:com.google.active_minutes:com.google.android.gms:merge_active_minutes"
                        ],
                        [
                            "dataTypeName" => "com.google.distance.delta",
                            "dataSourceId" => "derived:com.google.distance.delta:com.google.android.gms:merge_distance_delta"
                        ]
                    ],
                    "endTimeMillis" => $endTime,
                    "startTimeMillis" => $startTime
                ]);

            if ($response->successful()) {
                return $this->parseActivityData($response->json());
            }

            Log::error('Google Fit Activity API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Google Fit Activity API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение данных о сердечном ритме
     */
    public function getHeartRateData($accessToken, $startTime, $endTime): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post('https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate', [
                    "aggregateBy" => [
                        [
                            "dataTypeName" => "com.google.heart_rate.bpm",
                            "dataSourceId" => "derived:com.google.heart_rate.bpm:com.google.android.gms:merge_heart_rate_bpm"
                        ]
                    ],
                    "endTimeMillis" => $endTime,
                    "startTimeMillis" => $startTime
                ]);

            if ($response->successful()) {
                return $this->parseHeartRateData($response->json());
            }

            Log::error('Google Fit Heart Rate API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Google Fit Heart Rate API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Парсинг данных о сне
     */
    private function parseSleepData(array $data, $startTime, $endTime): array
    {
        $sleepData = [
            'total_sleep_minutes' => 0,
            'deep_sleep_minutes' => 0,
            'light_sleep_minutes' => 0,
            'rem_sleep_minutes' => 0,
            'awake_minutes' => 0,
            'sleep_efficiency' => 0,
            'sleep_phases' => [],
            'bedtime' => null,
            'wakeup_time' => null
        ];

        if (!isset($data['bucket']) || empty($data['bucket'])) {
            return $sleepData;
        }

        $phases = [];
        $firstSleepTime = null;
        $lastWakeTime = null;

        foreach ($data['bucket'] as $bucket) {
            if (isset($bucket['dataset'])) {
                foreach ($bucket['dataset'] as $dataset) {
                    if (isset($dataset['point'])) {
                        foreach ($dataset['point'] as $point) {
                            $sleepStage = $point['value'][0]['intVal'] ?? 1;
                            $startTimeNanos = $point['startTimeNanos'];
                            $endTimeNanos = $point['endTimeNanos'];

                            $startTimeMs = $startTimeNanos / 1000000;
                            $endTimeMs = $endTimeNanos / 1000000;

                            $duration = ($endTimeMs - $startTimeMs) / 60000; // в минутах

                            $phase = [
                                'stage' => $sleepStage,
                                'stage_name' => $this->getSleepStageName($sleepStage),
                                'start_time' => $startTimeMs,
                                'end_time' => $endTimeMs,
                                'duration_minutes' => round($duration, 1),
                                'start_datetime' => date('Y-m-d H:i:s', $startTimeMs / 1000),
                                'end_datetime' => date('Y-m-d H:i:s', $endTimeMs / 1000)
                            ];

                            $phases[] = $phase;

                            // Определяем время отхода ко сну и пробуждения
                            if ($firstSleepTime === null || $startTimeMs < $firstSleepTime) {
                                $firstSleepTime = $startTimeMs;
                            }
                            if ($lastWakeTime === null || $endTimeMs > $lastWakeTime) {
                                $lastWakeTime = $endTimeMs;
                            }

                            // Суммируем время по фазам
                            switch ($sleepStage) {
                                case 4: // REM sleep
                                    $sleepData['rem_sleep_minutes'] += $duration;
                                    break;
                                case 3: // Deep sleep
                                    $sleepData['deep_sleep_minutes'] += $duration;
                                    break;
                                case 2: // Light sleep
                                    $sleepData['light_sleep_minutes'] += $duration;
                                    break;
                                case 1: // Awake
                                    $sleepData['awake_minutes'] += $duration;
                                    break;
                                case 5: // Out of bed (для некоторых устройств)
                                    $sleepData['awake_minutes'] += $duration;
                                    break;
                            }
                        }
                    }
                }
            }
        }

        // Общее время сна (без времени бодрствования)
        $sleepData['total_sleep_minutes'] = $sleepData['deep_sleep_minutes'] +
            $sleepData['light_sleep_minutes'] +
            $sleepData['rem_sleep_minutes'];

        // Эффективность сна
        $totalTimeInBed = $sleepData['total_sleep_minutes'] + $sleepData['awake_minutes'];
        if ($totalTimeInBed > 0) {
            $sleepData['sleep_efficiency'] = round(($sleepData['total_sleep_minutes'] / $totalTimeInBed) * 100);
        }

        // Время отхода ко сну и пробуждения
        if ($firstSleepTime) {
            $sleepData['bedtime'] = date('H:i', $firstSleepTime / 1000);
        }
        if ($lastWakeTime) {
            $sleepData['wakeup_time'] = date('H:i', $lastWakeTime / 1000);
        }

        $sleepData['sleep_phases'] = $phases;

        return $sleepData;
    }

    /**
     * Получение названия фазы сна
     */
    private function getSleepStageName(int $stage): string
    {
        return match($stage) {
            1 => 'awake',
            2 => 'light',
            3 => 'deep',
            4 => 'rem',
            5 => 'out_of_bed',
            default => 'unknown'
        };
    }

    /**
     * Парсинг данных об активности
     */
    private function parseActivityData(array $data): array
    {
        $activityData = [
            'steps' => 0,
            'calories' => 0,
            'active_minutes' => 0,
            'distance_km' => 0,
            'heart_points' => 0
        ];

        if (!isset($data['bucket']) || empty($data['bucket'])) {
            return $activityData;
        }

        foreach ($data['bucket'] as $bucket) {
            if (isset($bucket['dataset'])) {
                foreach ($bucket['dataset'] as $dataset) {
                    $dataType = $dataset['dataSourceId'] ?? '';
                    $value = 0;

                    if (isset($dataset['point'])) {
                        foreach ($dataset['point'] as $point) {
                            if (isset($point['value'])) {
                                foreach ($point['value'] as $val) {
                                    $value += $val['intVal'] ?? $val['fpVal'] ?? 0;
                                }
                            }
                        }
                    }

                    // Определяем тип данных
                    if (str_contains($dataType, 'step_count')) {
                        $activityData['steps'] = (int) $value;
                    } elseif (str_contains($dataType, 'calories')) {
                        $activityData['calories'] = (int) $value;
                    } elseif (str_contains($dataType, 'active_minutes')) {
                        $activityData['active_minutes'] = (int) $value;
                    } elseif (str_contains($dataType, 'distance')) {
                        $activityData['distance_km'] = round($value / 1000, 2); // метры в км
                    }
                }
            }
        }

        return $activityData;
    }

    /**
     * Парсинг данных о сердечном ритме
     */
    private function parseHeartRateData(array $data): array
    {
        $heartRateData = [
            'average_bpm' => 0,
            'max_bpm' => 0,
            'min_bpm' => 0,
            'resting_heart_rate' => 0,
            'readings' => []
        ];

        if (!isset($data['bucket']) || empty($data['bucket'])) {
            return $heartRateData;
        }

        $readings = [];
        $totalBpm = 0;
        $readingCount = 0;

        foreach ($data['bucket'] as $bucket) {
            if (isset($bucket['dataset'])) {
                foreach ($bucket['dataset'] as $dataset) {
                    if (isset($dataset['point'])) {
                        foreach ($dataset['point'] as $point) {
                            if (isset($point['value'][0]['fpVal'])) {
                                $bpm = $point['value'][0]['fpVal'];
                                $timestamp = $point['startTimeNanos'] / 1000000;

                                $readings[] = [
                                    'bpm' => $bpm,
                                    'timestamp' => $timestamp,
                                    'datetime' => date('Y-m-d H:i:s', $timestamp / 1000)
                                ];

                                $totalBpm += $bpm;
                                $readingCount++;

                                if ($bpm > $heartRateData['max_bpm']) {
                                    $heartRateData['max_bpm'] = $bpm;
                                }
                                if ($heartRateData['min_bpm'] === 0 || $bpm < $heartRateData['min_bpm']) {
                                    $heartRateData['min_bpm'] = $bpm;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($readingCount > 0) {
            $heartRateData['average_bpm'] = round($totalBpm / $readingCount);
            $heartRateData['resting_heart_rate'] = $this->calculateRestingHeartRate($readings);
            $heartRateData['readings'] = $readings;
        }

        return $heartRateData;
    }

    /**
     * Расчет пульса покоя (минимальное значение за ночь)
     */
    private function calculateRestingHeartRate(array $readings): int
    {
        if (empty($readings)) return 0;

        $nightReadings = array_filter($readings, function($reading) {
            $hour = date('H', $reading['timestamp'] / 1000);
            return $hour >= 2 && $hour <= 6; // С 2 до 6 утра
        });

        if (empty($nightReadings)) {
            $nightReadings = $readings;
        }

        $minBpm = min(array_column($nightReadings, 'bpm'));

        return (int) $minBpm;
    }

    /**
     * Проверка валидности access token
     */
    public function validateAccessToken($accessToken): bool
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/fitness/v1/users/me/dataSources');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Получение информации о пользователе
     */
    public function getUserInfo($accessToken): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google User Info Error: ' . $e->getMessage());
            return null;
        }
    }
}
