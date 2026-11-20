@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-white">
                        <i class="fas fa-bed me-2"></i>Детали записи сна
                    </h2>
                    <a href="{{ route('sleep.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Назад
                    </a>
                </div>

                <div class="dashboard-card p-4">
                    <!-- Заголовок с датой и качеством -->
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h4 class="mb-1">{{ $sleep->date->translatedFormat('d F Y') }}</h4>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($sleep->bedtime)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($sleep->wakeup_time)->format('H:i') }}
                            </small>
                        </div>
                        <span class="badge bg-{{ $sleep->sleep_quality >= 80 ? 'success' : ($sleep->sleep_quality >= 60 ? 'warning' : 'danger') }} fs-6">
                            {{ $sleep->sleep_quality }}%
                        </span>
                    </div>

                    <!-- Основная статистика -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="stat-card p-3 text-center">
                                <div class="stat-value text-white">{{ round($sleep->sleep_duration / 60, 1) }}ч</div>
                                <small class="text-white">Общая продолжительность</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="stat-card p-3 text-center">
                                <div class="stat-value text-white">{{ $sleep->sleep_efficiency }}%</div>
                                <small class="text-white">Эффективность сна</small>
                            </div>
                        </div>
                    </div>

                    <!-- Фазы сна -->
                    <div class="mb-4">
                        <h5 class="mb-3">Фазы сна</h5>
                        <div class="row">
                            <div class="col-6 col-md-3 mb-3">
                                <div class="text-center">
                                    <div class="text-success fw-bold">{{ $sleep->deep_sleep }} мин</div>
                                    <small class="text-muted">Глубокий сон</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="text-center">
                                    <div class="text-warning fw-bold">{{ $sleep->light_sleep }} мин</div>
                                    <small class="text-muted">Легкий сон</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="text-center">
                                    <div class="text-info fw-bold">{{ $sleep->rem_sleep }} мин</div>
                                    <small class="text-muted">REM сон</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="text-center">
                                    <div class="text-secondary fw-bold">{{ $sleep->awake_time }} мин</div>
                                    <small class="text-muted">Бодрствование</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Анализ сна -->
                    @if(!empty($sleepAnalysis))
                        <div class="mb-4">
                            <h5 class="mb-3">Анализ сна</h5>
                            <div class="alert alert-light">
                                <ul class="mb-0">
                                    @foreach($sleepAnalysis as $analysis)
                                        <li>{{ $analysis }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Заметки -->
                    @if($sleep->notes)
                        <div class="mb-4">
                            <h5 class="mb-3">Заметки</h5>
                            <div class="alert alert-light">
                                <i class="fas fa-sticky-note me-2 text-muted"></i>
                                {{ $sleep->notes }}
                            </div>
                        </div>
                    @endif

                    <!-- Кнопки действий -->
                    <div class="d-flex gap-2">
                        <a href="{{ route('sleep.edit', $sleep) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>Редактировать
                        </a>
                        <form action="{{ route('sleep.destroy', $sleep) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Удалить эту запись?')">
                                <i class="fas fa-trash me-1"></i>Удалить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
