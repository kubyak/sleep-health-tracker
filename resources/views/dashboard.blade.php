@extends('layouts.app')

@section('title', 'Главная - SleepTracker')

@section('content')
    <div class="fade-in">
        <!-- Приветствие -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="glass-card p-4 text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2 fw-bold">Добро пожаловать, {{ auth()->user()->name }}! 👋</h1>
                            <p class="mb-0 opacity-75">Сегодня {{ now()->translatedFormat('d F Y') }}. Хорошего дня!</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="pulse-animation">
                                <div class="stat-value">{{ $healthData->profile_completion_percentage ?? 0 }}%</div>
                                <small class="opacity-75">Заполнение профиля</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card p-4">
                    <h5 class="card-title mb-4 d-flex align-items-center">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Быстрые действия
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="{{ route('sleep.create') }}" class="btn btn-primary w-100 py-3 hover-lift d-flex flex-column align-items-center">
                                <i class="fas fa-bed fa-2x mb-2"></i>
                                <span>Добавить сон</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('activity.create') }}" class="btn btn-success w-100 py-3 hover-lift d-flex flex-column align-items-center">
                                <i class="fas fa-running fa-2x mb-2"></i>
                                <span>Активность</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('recommendations') }}" class="btn btn-info w-100 py-3 hover-lift d-flex flex-column align-items-center text-white">
                                <i class="fas fa-heart fa-2x mb-2"></i>
                                <span>Рекомендации</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('sleep.index') }}" class="btn btn-warning w-100 py-3 hover-lift d-flex flex-column align-items-center">
                                <i class="fas fa-history fa-2x mb-2"></i>
                                <span>История</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основные карточки -->
        <div class="row g-4 mb-4">
            <!-- Сон сегодня -->
            <div class="col-md-6">
                <div class="dashboard-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-bed text-primary me-2"></i>
                            Сон сегодня
                        </h5>
                        <span class="badge bg-primary">Сегодня</span>
                    </div>

                    @if($sleepToday)
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="stat-value text-primary">{{ round($sleepToday->sleep_duration / 60, 1) }}ч</div>
                                <small class="text-muted">Длительность</small>
                            </div>
                            <div class="col-4">
                                <div class="stat-value text-success">{{ $sleepToday->sleep_quality }}%</div>
                                <small class="text-muted">Качество</small>
                            </div>
                            <div class="col-4">
                                <div class="stat-value text-info">{{ $sleepToday->deep_sleep ?? 0 }}м</div>
                                <small class="text-muted">Глубокий</small>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1 small fw-medium">
                                <span>Качество сна</span>
                                <span>{{ $sleepToday->sleep_quality }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar progress-bar-custom" style="width: {{ $sleepToday->sleep_quality }}%"></div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Нет данных о сне за сегодня</p>
                            <a href="{{ route('sleep.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Добавить запись
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Активность сегодня -->
            <div class="col-md-6">
                <div class="dashboard-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-running text-success me-2"></i>
                            Активность сегодня
                        </h5>
                        <span class="badge bg-success">Сегодня</span>
                    </div>

                    @if($activityToday)
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="stat-value text-success">{{ number_format($activityToday->steps) }}</div>
                                <small class="text-muted">Шаги</small>
                            </div>
                            <div class="col-4">
                                <div class="stat-value text-warning">{{ $activityToday->calories_burned }}</div>
                                <small class="text-muted">Калории</small>
                            </div>
                            <div class="col-4">
                                <div class="stat-value text-info">{{ $activityToday->active_minutes }}м</div>
                                <small class="text-muted">Активно</small>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1 small fw-medium">
                                <span>Цель: 10,000 шагов</span>
                                <span>{{ min(100, ($activityToday->steps / 10000) * 100) }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ min(100, ($activityToday->steps / 10000) * 100) }}%"></div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-running fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Нет данных об активности за сегодня</p>
                            <a href="{{ route('activity.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Добавить активность
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Статистика и графики -->
        <div class="row g-4 mb-4">
            <!-- Статистика сна -->
            <div class="col-md-6">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Статистика сна
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" data-period="week">Неделя</button>
                            <button class="btn btn-outline-primary" data-period="month">Месяц</button>
                        </div>
                    </div>

                    <div class="row text-center mb-4">
                        <div class="col-4 mb-3">
                            <div class="stat-card">
                                <div class="h4 mb-1 fw-bold">{{ $sleepStats['avg_duration'] }}ч</div>
                                <small>Длительность</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="stat-card">
                                <div class="h4 mb-1 fw-bold">{{ $sleepStats['avg_quality'] }}%</div>
                                <small>Качество</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="stat-card">
                                <div class="h4 mb-1 fw-bold">{{ round($sleepStats['consistency']) }}%</div>
                                <small>Регулярность</small>
                            </div>
                        </div>
                    </div>

                    <canvas id="sleepChart" height="200"></canvas>
                </div>
            </div>

            <!-- Статистика активности -->
            <div class="col-md-6">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-chart-bar text-success me-2"></i>
                            Активность
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success active" data-period="week">Неделя</button>
                            <button class="btn btn-outline-success" data-period="month">Месяц</button>
                        </div>
                    </div>

                    <div class="row text-center mb-4">
                        <div class="col-4 mb-3">
                            <div class="stat-card">
                                <div class="h4 mb-1 fw-bold">{{ number_format($activityStats['avg_steps']) }}</div>
                                <small>Шаги</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="stat-card">
                                <div class="h4 mb-1 fw-bold">{{ $activityStats['avg_calories'] }}</div>
                                <small>Калории</small>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="stat-card">
                                <div class="h4 mb-1 fw-bold">{{ $activityStats['active_days'] }}/7</div>
                                <small>Активные дни</small>
                            </div>
                        </div>
                    </div>

                    <canvas id="activityChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Рекомендации -->
        @if($recommendations->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="dashboard-card p-4">
                        <h5 class="card-title mb-4 d-flex align-items-center">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            Персональные рекомендации
                        </h5>
                        <div class="row">
                            @foreach($recommendations as $recommendation)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-0 h-100 hover-lift">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0">
                                                    <i class="{{ $recommendation->icon }} fa-2x text-{{ $recommendation->category_color }}"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="card-title mb-2 fw-bold">{{ $recommendation->title }}</h6>
                                                    <p class="card-text small text-muted mb-2">{{ Str::limit($recommendation->description, 120) }}</p>
                                                    <span class="badge bg-{{ $recommendation->category_color }}">
                                            {{ $recommendation->formatted_category }}
                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('recommendations') }}" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-right me-2"></i>Все рекомендации
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        // График сна
        const sleepCtx = document.getElementById('sleepChart').getContext('2d');
        const sleepChart = new Chart(sleepCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($sleepWeek->pluck('date')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d.m');
        })) !!},
                datasets: [{
                    label: 'Качество сна (%)',
                    data: {!! json_encode($sleepWeek->pluck('sleep_quality')) !!},
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // График активности
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($activityWeek->pluck('date')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d.m');
        })) !!},
                datasets: [{
                    label: 'Шаги',
                    data: {!! json_encode($activityWeek->pluck('steps')) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Переключение периодов
        document.querySelectorAll('[data-period]').forEach(button => {
            button.addEventListener('click', function() {
                const period = this.dataset.period;

                // Обновляем активные кнопки
                this.parentElement.querySelectorAll('.btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');

                // Загружаем новые данные
                loadChartData(period);
            });
        });

        function loadChartData(period) {
            axios.get('/chart-data?period=' + period)
                .then(response => {
                    updateCharts(response.data);
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                });
        }

        function updateCharts(data) {
            // Обновляем график сна
            sleepChart.data.labels = data.labels;
            sleepChart.data.datasets[0].data = Object.values(data.sleep).map(s => s.quality);
            sleepChart.update();

            // Обновляем график активности
            activityChart.data.labels = data.labels;
            activityChart.data.datasets[0].data = Object.values(data.activity).map(a => a.steps);
            activityChart.update();
        }
    </script>
@endsection
