@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-white">
                        <i class="fas fa-bed me-2"></i>История сна
                    </h2>
                    <a href="{{ route('sleep.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Добавить запись
                    </a>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        @if($sleepStats['total_records'] > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-card p-4">
                        <h5 class="card-title mb-3">Общая статистика сна</h5>
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="stat-value text-primary">{{ $sleepStats['avg_duration'] }}ч</div>
                                <small class="text-muted">Средняя длительность</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="stat-value text-success">{{ $sleepStats['avg_quality'] }}%</div>
                                <small class="text-muted">Среднее качество</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="stat-value text-info">{{ $sleepStats['total_records'] }}</div>
                                <small class="text-muted">Всего записей</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="stat-value text-warning">{{ $sleepStats['best_quality'] }}%</div>
                                <small class="text-muted">Лучшее качество</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Записи сна -->
        <div class="row">
            @forelse($sleepRecords as $record)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="dashboard-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">{{ $record->date->translatedFormat('d F Y') }}</h5>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($record->bedtime)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($record->wakeup_time)->format('H:i') }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $record->sleep_quality >= 80 ? 'success' : ($record->sleep_quality >= 60 ? 'warning' : 'danger') }}">
                        {{ $record->sleep_quality }}%
                    </span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span>Длительность:</span>
                                <strong>{{ round($record->sleep_duration / 60, 1) }} часов</strong>
                            </div>
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span>Глубокий сон:</span>
                                <strong>{{ $record->deep_sleep ?? 0 }} мин</strong>
                            </div>
                            <div class="d-flex justify-content-between text-sm">
                                <span>REM сон:</span>
                                <strong>{{ $record->rem_sleep ?? 0 }} мин</strong>
                            </div>
                        </div>

                        @if($record->notes)
                            <div class="alert alert-light py-2 px-3 small mb-3">
                                <i class="fas fa-sticky-note me-1 text-muted"></i>
                                {{ Str::limit($record->notes, 100) }}
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <a href="{{ route('sleep.show', $record) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>Подробнее
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="dashboard-card p-5 text-center">
                        <i class="fas fa-bed fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Нет записей о сне</h4>
                        <p class="text-muted mb-4">Начните отслеживать свой сон для улучшения здоровья</p>
                        <a href="{{ route('sleep.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Добавить первую запись
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Пагинация -->
        @if($sleepRecords->hasPages())
            <div class="row mt-4">
                <div class="col-12">
                    <div class="dashboard-card p-3">
                        {{ $sleepRecords->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
