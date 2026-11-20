@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-white">
                        <i class="fas fa-edit me-2"></i>Редактировать запись сна
                    </h2>
                    <a href="{{ route('sleep.show', $sleep) }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Назад
                    </a>
                </div>

                <div class="dashboard-card p-4">
                    <form action="{{ route('sleep.update', $sleep) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label text-black">Дата</label>
                                <input type="date" class="form-control" id="date" name="date"
                                       value="{{ old('date', $sleep->date->format('Y-m-d')) }}" required>
                                @error('date')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sleep_quality" class="form-label text-black">Качество сна (%)</label>
                                <input type="number" class="form-control" id="sleep_quality" name="sleep_quality"
                                       min="0" max="100" value="{{ old('sleep_quality', $sleep->sleep_quality) }}">
                                @error('sleep_quality')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bedtime" class="form-label text-black">Время отхода ко сну</label>
                                <input type="time" class="form-control" id="bedtime" name="bedtime"
                                       value="{{ old('bedtime', $sleep->bedtime) }}" required>
                                @error('bedtime')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="wakeup_time" class="form-label text-black">Время пробуждения</label>
                                <input type="time" class="form-control" id="wakeup_time" name="wakeup_time"
                                       value="{{ old('wakeup_time', $sleep->wakeup_time) }}" required>
                                @error('wakeup_time')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="deep_sleep" class="form-label text-black">Глубокий сон (мин)</label>
                                <input type="number" class="form-control" id="deep_sleep" name="deep_sleep"
                                       min="0" value="{{ old('deep_sleep', $sleep->deep_sleep) }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="light_sleep" class="form-label text-black">Легкий сон (мин)</label>
                                <input type="number" class="form-control" id="light_sleep" name="light_sleep"
                                       min="0" value="{{ old('light_sleep', $sleep->light_sleep) }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="rem_sleep" class="form-label text-black">REM сон (мин)</label>
                                <input type="number" class="form-control" id="rem_sleep" name="rem_sleep"
                                       min="0" value="{{ old('rem_sleep', $sleep->rem_sleep) }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="awake_time" class="form-label text-black">Бодрствование (мин)</label>
                                <input type="number" class="form-control" id="awake_time" name="awake_time"
                                       min="0" value="{{ old('awake_time', $sleep->awake_time) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label text-black">Заметки</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Дополнительные заметки о сне...">{{ old('notes', $sleep->notes) }}</textarea>
                            @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Сохранить изменения
                            </button>
                            <a href="{{ route('sleep.show', $sleep) }}" class="btn btn-outline-light">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
