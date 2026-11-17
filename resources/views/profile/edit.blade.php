@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-user-circle text-primary me-2"></i>Профиль пользователя
                        </h2>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Назад
                        </a>
                    </div>

                    <!-- Информация о прогрессе -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="glass-card p-3 text-purple">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-1">Заполнение профиля</h5>
                                        <small>Чем полнее профиль, тем точнее рекомендации</small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="display-6 fw-bold">{{ $healthData->profile_completion_percentage ?? 0 }}%</div>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar progress-bar-custom"
                                         style="width: {{ $healthData->profile_completion_percentage ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Основная информация -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Основная информация
                                </h5>
                            </div>

                            <div class="col-md-4">
                                <label for="age" class="form-label">Возраст</label>
                                <input type="number" class="form-control @error('age') is-invalid @enderror"
                                       id="age" name="age" value="{{ old('age', $healthData->age) }}" min="1" max="120">
                                @error('age')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="weight" class="form-label">Вес (кг)</label>
                                <input type="number" step="0.1" class="form-control @error('weight') is-invalid @enderror"
                                       id="weight" name="weight" value="{{ old('weight', $healthData->weight) }}" min="1" max="300">
                                @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="height" class="form-label">Рост (см)</label>
                                <input type="number" step="0.1" class="form-control @error('height') is-invalid @enderror"
                                       id="height" name="height" value="{{ old('height', $healthData->height) }}" min="50" max="250">
                                @error('height')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="gender" class="form-label">Пол</label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                    <option value="">Выберите пол</option>
                                    <option value="male" {{ old('gender', $healthData->gender) == 'male' ? 'selected' : '' }}>Мужской</option>
                                    <option value="female" {{ old('gender', $healthData->gender) == 'female' ? 'selected' : '' }}>Женский</option>
                                    <option value="other" {{ old('gender', $healthData->gender) == 'other' ? 'selected' : '' }}>Другой</option>
                                </select>
                                @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="blood_type" class="form-label">Группа крови</label>
                                <select class="form-select @error('blood_type') is-invalid @enderror" id="blood_type" name="blood_type">
                                    <option value="">Выберите группу крови</option>
                                    <option value="A+" {{ old('blood_type', $healthData->blood_type) == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('blood_type', $healthData->blood_type) == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('blood_type', $healthData->blood_type) == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('blood_type', $healthData->blood_type) == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('blood_type', $healthData->blood_type) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('blood_type', $healthData->blood_type) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ old('blood_type', $healthData->blood_type) == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('blood_type', $healthData->blood_type) == 'O-' ? 'selected' : '' }}>O-</option>
                                </select>
                                @error('blood_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Цели здоровья -->
                            <div class="col-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-bullseye me-2"></i>Цели здоровья
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label for="sleep_goal" class="form-label">Цель сна (минуты)</label>
                                <input type="number" class="form-control @error('sleep_goal') is-invalid @enderror"
                                       id="sleep_goal" name="sleep_goal" value="{{ old('sleep_goal', $healthData->sleep_goal) }}" min="240" max="720">
                                <small class="form-text text-muted">Рекомендуется: 480 минут (8 часов)</small>
                                @error('sleep_goal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="steps_goal" class="form-label">Цель шагов (в день)</label>
                                <input type="number" class="form-control @error('steps_goal') is-invalid @enderror"
                                       id="steps_goal" name="steps_goal" value="{{ old('steps_goal', $healthData->steps_goal) }}" min="1000" max="50000">
                                <small class="form-text text-muted">Рекомендуется: 10,000 шагов</small>
                                @error('steps_goal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="calories_goal" class="form-label">Цель калорий (в день)</label>
                                <input type="number" class="form-control @error('calories_goal') is-invalid @enderror"
                                       id="calories_goal" name="calories_goal" value="{{ old('calories_goal', $healthData->calories_goal) }}" min="100" max="2000">
                                <small class="form-text text-muted">Рекомендуется: 500 калорий</small>
                                @error('calories_goal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="active_minutes_goal" class="form-label">Цель активных минут</label>
                                <input type="number" class="form-control @error('active_minutes_goal') is-invalid @enderror"
                                       id="active_minutes_goal" name="active_minutes_goal" value="{{ old('active_minutes_goal', $healthData->active_minutes_goal) }}" min="10" max="300">
                                <small class="form-text text-muted">Рекомендуется: 30 минут</small>
                                @error('active_minutes_goal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Медицинская информация -->
                            <div class="col-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-heartbeat me-2"></i>Медицинская информация
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label for="resting_heart_rate" class="form-label">Пульс в покое (уд/мин)</label>
                                <input type="number" class="form-control @error('resting_heart_rate') is-invalid @enderror"
                                       id="resting_heart_rate" name="resting_heart_rate" value="{{ old('resting_heart_rate', $healthData->resting_heart_rate) }}" min="40" max="120">
                                @error('resting_heart_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Артериальное давление</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="blood_pressure_systolic"
                                               placeholder="Систолическое" value="{{ old('blood_pressure_systolic', $healthData->blood_pressure_systolic) }}" min="80" max="200">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="blood_pressure_diastolic"
                                               placeholder="Диастолическое" value="{{ old('blood_pressure_diastolic', $healthData->blood_pressure_diastolic) }}" min="40" max="120">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="emergency_contact" class="form-label">Экстренный контакт</label>
                                <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror"
                                       id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $healthData->emergency_contact) }}">
                                @error('emergency_contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Кнопки -->
                            <div class="col-12 mt-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill py-2">
                                        <i class="fas fa-save me-2"></i>Сохранить профиль
                                    </button>
                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                        Отмена
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Статистика здоровья -->
                    @if($healthData->is_profile_complete)
                        <div class="row mt-5">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-chart-bar me-2"></i>Ваша статистика здоровья
                                </h5>

                                <div class="row text-center">
                                    @if($healthData->bmi)
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="stat-card">
                                                <div class="h4 mb-1">{{ $healthData->bmi }}</div>
                                                <small>ИМТ</small>
                                                <div class="mt-1">
                                        <span class="badge bg-{{ $healthData->bmi_color }}">
                                            {{ $healthData->bmi_description }}
                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($healthData->basal_metabolic_rate)
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="stat-card">
                                                <div class="h4 mb-1">{{ $healthData->basal_metabolic_rate }}</div>
                                                <small>BMR (ккал)</small>
                                                <div class="mt-1">
                                                    <small>Базальный метаболизм</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($healthData->recommended_calorie_intake)
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="stat-card">
                                                <div class="h4 mb-1">{{ $healthData->recommended_calorie_intake }}</div>
                                                <small>Рек. калории</small>
                                                <div class="mt-1">
                                                    <small>В день</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="stat-card">
                                            <div class="h4 mb-1">{{ $healthData->recommended_water_intake }}л</div>
                                            <small>Рек. вода</small>
                                            <div class="mt-1">
                                                <small>В день</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Автоматический расчет BMR при изменении данных
                const ageInput = document.getElementById('age');
                const weightInput = document.getElementById('weight');
                const heightInput = document.getElementById('height');
                const genderSelect = document.getElementById('gender');

                function calculateBMR() {
                    if (ageInput.value && weightInput.value && heightInput.value && genderSelect.value) {
                        // Здесь можно добавить расчет BMR для отображения
                        console.log('Все данные для расчета BMR заполнены');
                    }
                }

                ageInput.addEventListener('input', calculateBMR);
                weightInput.addEventListener('input', calculateBMR);
                heightInput.addEventListener('input', calculateBMR);
                genderSelect.addEventListener('change', calculateBMR);
            });
        </script>
    @endsection
@endsection
