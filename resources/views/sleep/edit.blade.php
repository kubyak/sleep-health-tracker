@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-edit text-warning me-2"></i>Редактировать запись о сне
                        </h2>
                        <a href="{{ route('sleep.show', $sleep) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Назад
                        </a>
                    </div>

                    <form action="{{ route('sleep.update', $sleep) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Дата *</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                       id="date" name="date" value="{{ old('date', $sleep->date->format('Y-m-d')) }}" required>
                                @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="bedtime" class="form-label">Время отхода ко сну *</label>
                                <input type="time" class="form-control @error('bedtime') is-invalid @enderror"
                                       id="bedtime" name="bedtime" value="{{ old('bedtime', $sleep->bedtime->format('H:i')) }}" required>
                                @error('bedtime')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="wakeup_time" class="form-label">Время пробуждения *</label>
                                <input type="time" class="form-control @error('wakeup_time') is-invalid @enderror"
                                       id="wakeup_time" name="wakeup_time" value="{{ old('wakeup_time', $sleep->wakeup_time->format('H:i')) }}" required>
                                @error('wakeup_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Фазы сна (в минутах)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="deep_sleep"
                                               placeholder="Глубокий" value="{{ old('deep_sleep', $sleep->deep_sleep) }}" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="rem_sleep"
                                               placeholder="REM" value="{{ old('rem_sleep', $sleep->rem_sleep) }}" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="light_sleep"
                                               placeholder="Легкий сон" value="{{ old('light_sleep', $sleep->light_sleep) }}" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="awake_time"
                                               placeholder="Бодрствование" value="{{ old('awake_time', $sleep->awake_time) }}" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label">Заметки</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="3"
                                          placeholder="Как вы себя чувствовали? Что повлияло на сон?">{{ old('notes', $sleep->notes) }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Текущее качество сна: <strong>{{ $sleep->sleep_quality }}%</strong>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning flex-fill py-2">
                                        <i class="fas fa-save me-2"></i>Обновить запись
                                    </button>
                                    <a href="{{ route('sleep.show', $sleep) }}" class="btn btn-outline-secondary">
                                        Отмена
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
