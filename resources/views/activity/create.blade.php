@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-running text-success me-2"></i>Добавить активность
                        </h2>
                        <a href="{{ route('activity.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Назад
                        </a>
                    </div>

                    <form action="{{ route('activity.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Дата *</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                       id="date" name="date" value="{{ old('date', $today) }}" required>
                                @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="workout_type" class="form-label">Тип активности</label>
                                <select class="form-select @error('workout_type') is-invalid @enderror"
                                        id="workout_type" name="workout_type">
                                    @foreach($workoutTypes as $value => $label)
                                        <option value="{{ $value }}" {{ old('workout_type') == $value ? 'selected' : '' }}>
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
                                       id="steps" name="steps" value="{{ old('steps', 0) }}" required min="0">
                                @error('steps')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="calories_burned" class="form-label">Сожжено калорий *</label>
                                <input type="number" class="form-control @error('calories_burned') is-invalid @enderror"
                                       id="calories_burned" name="calories_burned"
                                       value="{{ old('calories_burned', 0) }}" required min="0">
                                @error('calories_burned')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="active_minutes" class="form-label">Активные минуты *</label>
                                <input type="number" class="form-control @error('active_minutes') is-invalid @enderror"
                                       id="active_minutes" name="active_minutes"
                                       value="{{ old('active_minutes', 0) }}" required min="0">
                                @error('active_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="distance" class="form-label">Дистанция (км) *</label>
                                <input type="number" step="0.1" class="form-control @error('distance') is-invalid @enderror"
                                       id="distance" name="distance" value="{{ old('distance', 0) }}" required min="0">
                                @error('distance')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="heart_rate" class="form-label">Пульс (уд/мин)</label>
                                <input type="number" class="form-control @error('heart_rate') is-invalid @enderror"
                                       id="heart_rate" name="heart_rate" value="{{ old('heart_rate') }}" min="40" max="200">
                                @error('heart_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label">Заметки</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="3"
                                          placeholder="Опишите вашу активность, самочувствие...">{{ old('notes') }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Совет:</strong> Для точных данных используйте фитнес-трекер или умные часы.
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success w-100 py-2">
                                    <i class="fas fa-plus me-2"></i>Добавить активность
                                </button>
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
                        if (!caloriesInput.value || caloriesInput.value == 0) {
                            caloriesInput.value = calculatedCalories;
                        }
                    }
                }

                stepsInput.addEventListener('input', calculateCalories);
                workoutTypeSelect.addEventListener('change', calculateCalories);
            });
        </script>
    @endsection
@endsection
