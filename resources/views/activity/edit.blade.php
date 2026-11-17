@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-edit text-warning me-2"></i>Редактировать активность
                        </h2>
                        <a href="{{ route('activity.show', $activity) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Назад
                        </a>
                    </div>

                    <form action="{{ route('activity.update', $activity) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Дата *</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                       id="date" name="date" value="{{ old('date', $activity->date->format('Y-m-d')) }}" required>
                                @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="workout_type" class="form-label">Тип активности</label>
                                <select class="form-select @error('workout_type') is-invalid @enderror"
                                        id="workout_type" name="workout_type">
                                    @foreach($workoutTypes as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('workout_type', $activity->workout_type) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('workout_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="steps" class="form-label">Шаги *</label>
                                <input type="number" class="form-control @error('steps') is-invalid @enderror"
                                       id="steps" name="steps" value="{{ old('steps', $activity->steps) }}" required min="0">
                                @error('steps')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="calories_burned" class="form-label">Сожжено калорий *</label>
                                <input type="number" class="form-control @error('calories_burned') is-invalid @enderror"
                                       id="calories_burned" name="calories_burned"
                                       value="{{ old('calories_burned', $activity->calories_burned) }}" required min="0">
                                @error('calories_burned')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="active_minutes" class="form-label">Активные минуты *</label>
                                <input type="number" class="form-control @error('active_minutes') is-invalid @enderror"
                                       id="active_minutes" name="active_minutes"
                                       value="{{ old('active_minutes', $activity->active_minutes) }}" required min="0">
                                @error('active_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="distance" class="form-label">Дистанция (км) *</label>
                                <input type="number" step="0.1" class="form-control @error('distance') is-invalid @enderror"
                                       id="distance" name="distance" value="{{ old('distance', $activity->distance) }}" required min="0">
                                @error('distance')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="heart_rate" class="form-label">Пульс (уд/мин)</label>
                                <input type="number" class="form-control @error('heart_rate') is-invalid @enderror"
                                       id="heart_rate" name="heart_rate" value="{{ old('heart_rate', $activity->heart_rate) }}" min="40" max="200">
                                @error('heart_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label">Заметки</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="3"
                                          placeholder="Опишите вашу активность, самочувствие...">{{ old('notes', $activity->notes) }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning flex-fill py-2">
                                        <i class="fas fa-save me-2"></i>Обновить активность
                                    </button>
                                    <a href="{{ route('activity.show', $activity) }}" class="btn btn-outline-secondary">
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

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Автоматический расчет калорий на основе шагов и типа активности
                const stepsInput = document.getElementById('steps');
                const caloriesInput = document.getElementById('calories_burned');
                const workoutTypeSelect = document.getElementById('workout_type');

                function calculateCalories() {
                    if (stepsInput.value) {
                        const steps = parseInt(stepsInput.value);
                        const workoutType = workoutTypeSelect.value;

                        let caloriesPerStep = 0.04; // Базовое значение

                        // Корректировка в зависимости от типа активности
                        switch(workoutType) {
                            case 'running':
                                caloriesPerStep = 0.06;
                                break;
                            case 'cycling':
                                caloriesPerStep = 0.05;
                                break;
                            case 'swimming':
                                caloriesPerStep = 0.07;
                                break;
                            case 'gym':
                                caloriesPerStep = 0.08;
                                break;
                        }

                        const calculatedCalories = Math.round(steps * caloriesPerStep);
                        caloriesInput.value = calculatedCalories;
                    }
                }

                stepsInput.addEventListener('input', calculateCalories);
                workoutTypeSelect.addEventListener('change', calculateCalories);
            });
        </script>
    @endsection
@endsection
