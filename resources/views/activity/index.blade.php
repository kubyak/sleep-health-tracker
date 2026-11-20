@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-white">
                        <i class="fas fa-running me-2"></i>История активности
                    </h2>
                    <a href="{{ route('activity.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Добавить активность
                    </a>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        @if($activityStats['total_records'] > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-card p-4">
                        <h5 class="card-title mb-3">Общая статистика активности</h5>
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="stat-value text-success">{{ number_format($activityStats['avg_steps']) }}</div>
                                <small class="text-muted">Средние шаги</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="stat-value text-warning">{{ $activityStats['avg_calories'] }}</div>
                                <small class="text-muted">Средние калории</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="stat-value text-info">{{ $activityStats['avg_minutes'] }}м</div>
                                <small class="text-muted">Активные минуты</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="stat-value text-primary">{{ $activityStats['total_records'] }}</div>
                                <small class="text-muted">Всего записей</small>
                            </div>
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-md-6">
                                <div class="stat-value text-secondary">{{ $activityStats['total_distance'] }}км</div>
                                <small class="text-muted">Общая дистанция</small>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-value text-danger">{{ number_format($activityStats['max_steps']) }}</div>
                                <small class="text-muted">Макс. шаги за день</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Записи активности -->
        <div class="row">
            @forelse($activities as $activity)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="dashboard-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">{{ $activity->date->translatedFormat('d F Y') }}</h5>
                                <small class="text-muted">
                                    <i class="{{ $activity->workout_type_icon }} me-1"></i>
                                    {{ $activity->workout_type ? ucfirst($activity->workout_type) : 'Общая активность' }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $activity->getActivityLevel() == 'excellent' ? 'success' : ($activity->getActivityLevel() == 'good' ? 'warning' : 'secondary') }}">
                        {{ $activity->getActivityLevel() }}
                    </span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between text-sm mb-2">
                                <span><i class="fas fa-shoe-prints text-muted me-1"></i>Шаги:</span>
                                <strong>{{ number_format($activity->steps) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-sm mb-2">
                                <span><i class="fas fa-fire text-muted me-1"></i>Калории:</span>
                                <strong>{{ $activity->calories_burned }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-sm mb-2">
                                <span><i class="fas fa-clock text-muted me-1"></i>Активно:</span>
                                <strong>{{ $activity->active_minutes }} мин</strong>
                            </div>
                            <div class="d-flex justify-content-between text-sm">
                                <span><i class="fas fa-road text-muted me-1"></i>Дистанция:</span>
                                <strong>{{ $activity->distance }} км</strong>
                            </div>
                        </div>

                        @if($activity->heart_rate)
                            <div class="alert alert-danger py-2 px-3 small mb-3">
                                <i class="fas fa-heartbeat me-1"></i>
                                Пульс: {{ $activity->heart_rate }} уд/мин
                            </div>
                        @endif

                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ min(100, ($activity->steps / 10000) * 100) }}%"></div>
                        </div>
                        <small class="text-muted">Прогресс к цели 10,000 шагов</small>

                        
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="dashboard-card p-5 text-center">
                        <i class="fas fa-running fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Нет записей об активности</h4>
                        <p class="text-muted mb-4">Начните отслеживать свою активность для улучшения здоровья</p>
                        <a href="{{ route('activity.create') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-plus me-2"></i>Добавить первую запись
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Пагинация -->
        @if($activities->hasPages())
            <div class="row mt-4">
                <div class="col-12">
                    <div class="dashboard-card p-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
